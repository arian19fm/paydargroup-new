<?php

namespace Tests\Unit;

use Anthropic\Client;
use App\Support\News\NewsWriter;
use Carbon\Carbon;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

/** Runs the real SDK against a mocked HTTP transport (no network, no key). */
class NewsWriterTest extends TestCase
{
    /** @var list<array{request: RequestInterface}> */
    private array $history = [];

    private function writer(array $responses): NewsWriter
    {
        config(['services.anthropic.key' => 'test-key', 'news.model' => 'claude-opus-5-5', 'news.effort' => 'high', 'news.max_searches' => 6]);

        $stack = HandlerStack::create(new MockHandler(array_map(
            fn (array $body) => new Response(200, ['Content-Type' => 'application/json'], json_encode($body)),
            $responses
        )));
        $stack->push(Middleware::history($this->history));

        return new NewsWriter(new Client(apiKey: 'test-key', requestOptions: ['transporter' => new Guzzle(['handler' => $stack]), 'maxRetries' => 0]));
    }

    private function message(string $stopReason, array $content, int $in = 1000, int $out = 500, int $searches = 0): array
    {
        return [
            'id' => 'msg_'.uniqid(), 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-opus-5-5',
            'stop_reason' => $stopReason, 'stop_sequence' => null, 'content' => $content,
            'usage' => ['input_tokens' => $in, 'output_tokens' => $out, 'server_tool_use' => ['web_search_requests' => $searches, 'web_fetch_requests' => 0]],
        ];
    }

    public function test_it_searches_resumes_a_paused_turn_and_returns_the_submitted_articles(): void
    {
        $search = [
            ['type' => 'server_tool_use', 'id' => 'srvtoolu_1', 'name' => 'web_search', 'input' => ['query' => 'bitcoin price']],
            ['type' => 'web_search_tool_result', 'tool_use_id' => 'srvtoolu_1', 'content' => [
                ['type' => 'web_search_result', 'url' => 'https://www.reuters.com/markets/bitcoin', 'title' => 'Bitcoin', 'encrypted_content' => 'x', 'page_age' => null],
            ]],
        ];
        $article = ['title' => 'عنوان', 'slug' => 'bitcoin-news', 'excerpt' => 'لید', 'seo_title' => 'سئو', 'seo_description' => 'توضیح',
            'content_html' => '<p>متن</p>', 'sources' => [['title' => 'Bitcoin', 'url' => 'https://www.reuters.com/markets/bitcoin']]];

        $result = $this->writer([
            $this->message('pause_turn', $search, searches: 1),
            $this->message('tool_use', [['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'submit_articles', 'input' => ['articles' => [$article]]]], 2000, 3000),
        ])->write(1, ['رمزارز'], ['خبر قبلی'], Carbon::parse('2026-10-11 09:00', 'Asia/Tehran'));

        $this->assertCount(1, $result->drafts);
        $this->assertSame('bitcoin-news', $result->drafts[0]->slug);
        $this->assertSame(['https://www.reuters.com/markets/bitcoin'], $result->searchedUrls);
        $this->assertSame([3000, 3500, 1], [$result->inputTokens, $result->outputTokens, $result->webSearches]);

        $this->assertCount(2, $this->history);
        $request = $this->history[0]['request'];
        $body = json_decode((string) $request->getBody(), true);
        $this->assertStringEndsWith('/v1/messages', $request->getUri()->getPath());
        $this->assertStringContainsString('server-side-fallback-2026-07-01', $request->getHeaderLine('anthropic-beta'));
        $this->assertSame('claude-opus-5-5', $body['model']);
        $this->assertSame('default', $body['fallbacks']);
        $this->assertSame(['type' => 'adaptive'], $body['thinking']);
        $this->assertSame('high', $body['output_config']['effort']);
        $this->assertSame(['type' => 'web_search_20260209', 'name' => 'web_search', 'max_uses' => 6], $body['tools'][0]);
        $this->assertTrue($body['tools'][1]['strict']);
        $this->assertSame('submit_articles', $body['tools'][1]['name']);
        $this->assertArrayHasKey('input_schema', $body['tools'][1]);
        $this->assertStringContainsString('خبر قبلی', $body['messages'][0]['content']);
        $this->assertStringContainsString('Asia/Tehran', $body['messages'][0]['content']);

        // The resumed request carries the paused assistant turn unchanged.
        $second = json_decode((string) $this->history[1]['request']->getBody(), true);
        $this->assertSame('assistant', $second['messages'][1]['role']);
        $this->assertSame('web_search_tool_result', $second['messages'][1]['content'][1]['type']);
    }

    public function test_a_refusal_or_missing_submission_fails(): void
    {
        $this->expectExceptionMessage('without submitting');

        $this->writer([
            $this->message('end_turn', [['type' => 'text', 'text' => 'done']]),
            $this->message('end_turn', [['type' => 'text', 'text' => 'still nothing']]),
        ])->write(1, ['x'], [], now());
    }
}
