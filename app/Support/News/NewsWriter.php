<?php

namespace App\Support\News;

use Anthropic\Beta\Messages\BetaMessage;
use Anthropic\Client;
use Carbon\CarbonInterface;
use RuntimeException;

/**
 * Researches today's financial, investment and crypto news with Claude's
 * web search and returns SEO-ready Persian articles (docs/CMS.md →
 * "Automatic news").
 *
 * One Messages API request: Claude searches (server-side web_search tool),
 * then hands the finished articles to the strict `submit_articles` tool, so
 * the result is schema-valid JSON rather than free text. Long search loops
 * may come back as `pause_turn`; those are resumed a few times.
 */
class NewsWriter
{
    public const TOOL = 'submit_articles';

    protected const SYSTEM = <<<'PROMPT'
        You are the news editor of the Persian-language website of Paydar Group, an Iranian
        financial-technology holding (investment, crowdfunding, asset tokenisation). Each run you
        research the latest news on the given topics with web search and write original news
        articles in fluent, formal Persian for Iranian readers.

        Accuracy comes first. Report only what your search results support, attribute figures and
        claims to their source, and give dates explicitly (do not write "today" or "yesterday").
        Prefer reputable primary sources and news from the last 48 hours. Never invent numbers,
        quotes or events. Do not give personal investment advice or price predictions of your
        own; where useful, close with a neutral note that the article is informational.

        Write each article for search engines and for people:
        - title: a specific, informative Persian headline (about 50–70 characters) containing the
          main keyword; no clickbait, no site name.
        - slug: a short English, lowercase, hyphenated slug of the headline (3–7 words, a-z, 0-9, -).
        - excerpt: a 1–2 sentence Persian lead (about 140–160 characters) that answers what
          happened; it is shown under the headline and used as the meta description.
        - seo_title: at most 60 characters; seo_description: 140–155 characters, with the main keyword.
        - content_html: 500–900 words of semantic HTML. Start with one or two paragraphs that
          summarise the news, then 3–5 sections, each opened by an <h2> that reads as a natural
          question or topic; use <h3> for subsections, <ul>/<ol> for lists and <table> only for
          real tabular data, <strong> for key figures. Allowed tags: p, h2, h3, ul, ol, li, strong,
          em, blockquote, table, thead, tbody, tr, th, td, a. No <h1> (the headline is the page H1),
          no images, no inline styles, no scripts. Do not add a sources list; the site appends one.
        - sources: the pages you actually used, each with its title and exact URL from the results.

        Each article covers a different story. Skip stories in the list of recently published
        articles unless there is a substantial new development, and then say what is new.

        When the articles are ready, call submit_articles once with all of them.
        PROMPT;

    public function __construct(protected ?Client $client = null) {}

    /**
     * @param  list<string>  $topics
     * @param  list<string>  $recentTitles  headlines already published, to avoid repeats
     */
    public function write(int $count, array $topics, array $recentTitles, CarbonInterface $now): NewsResult
    {
        $messages = [['role' => 'user', 'content' => $this->brief($count, $topics, $recentTitles, $now)]];
        $input = $output = $searches = 0;
        $urls = [];
        $nudged = false;

        for ($round = 0; ; $round++) {
            $response = $this->send($messages);

            $input += $response->usage->inputTokens;
            $output += $response->usage->outputTokens;
            $searches += $response->usage->serverToolUse?->webSearchRequests ?? 0;
            $urls = [...$urls, ...$this->searchedUrls($response)];

            if ($response->stopReason === 'refusal') {
                throw new RuntimeException('Claude declined the request ('.($response->stopDetails?->category ?? 'refusal').').');
            }

            if ($submitted = $this->submittedArticles($response)) {
                $drafts = array_map(fn (array $item) => NewsDraft::fromToolInput($item), $submitted);

                return new NewsResult($drafts, array_values(array_unique($urls)), $response->model, $input, $output, $searches);
            }

            $messages[] = ['role' => 'assistant', 'content' => $response->content];

            if ($response->stopReason === 'pause_turn' && $round < (int) config('news.max_continuations')) {
                continue; // the server paused a long tool loop; resend to let it finish
            }

            if (! $nudged && in_array($response->stopReason, ['end_turn', 'pause_turn'], true)) {
                $nudged = true;
                $messages[] = ['role' => 'user', 'content' => 'Call '.self::TOOL.' now with the finished articles.'];

                continue;
            }

            throw new RuntimeException('Claude finished without submitting articles (stop reason: '.($response->stopReason ?? 'unknown').').');
        }
    }

    /** @param  list<array<string, mixed>>  $messages */
    protected function send(array $messages): BetaMessage
    {
        return $this->client()->beta->messages->create(
            maxTokens: (int) config('news.max_tokens'),
            messages: $messages,
            model: (string) config('news.model'),
            system: self::SYSTEM,
            thinking: ['type' => 'adaptive'],
            outputConfig: ['effort' => (string) config('news.effort')],
            tools: [
                ['type' => 'web_search_20260209', 'name' => 'web_search', 'maxUses' => (int) config('news.max_searches')],
                $this->submitTool(),
            ],
            // On a safety decline, the API retries on a suitable fallback model.
            fallbacks: 'default',
            betas: ['server-side-fallback-2026-07-01'],
        );
    }

    /** @param  list<string>  $topics */
    protected function brief(int $count, array $topics, array $recentTitles, CarbonInterface $now): string
    {
        $lines = [
            'Date and time now: '.$now->format('l j F Y, H:i').' ('.$now->timezone->getName().').',
            '',
            "Write {$count} news article".($count > 1 ? 's' : '').' on the most important recent news in these topics:',
            ...array_map(fn (string $t) => '- '.$t, $topics),
        ];

        if ($recentTitles !== []) {
            $lines[] = '';
            $lines[] = 'Recently published on the site (do not repeat):';
            array_push($lines, ...array_map(fn (string $t) => '- '.$t, $recentTitles));
        }

        return implode("\n", $lines);
    }

    protected function submitTool(): array
    {
        $string = fn (string $description) => ['type' => 'string', 'description' => $description];

        return [
            'name' => self::TOOL,
            'description' => 'Submit the finished news articles for publication on the site. Call once with every article.',
            'strict' => true,
            'inputSchema' => [
                'type' => 'object',
                'additionalProperties' => false,
                'required' => ['articles'],
                'properties' => [
                    'articles' => [
                        'type' => 'array',
                        'minItems' => 1,
                        'items' => [
                            'type' => 'object',
                            'additionalProperties' => false,
                            'required' => ['title', 'slug', 'excerpt', 'seo_title', 'seo_description', 'content_html', 'sources'],
                            'properties' => [
                                'title' => $string('Persian headline, about 50–70 characters.'),
                                'slug' => $string('English lowercase hyphenated slug.'),
                                'excerpt' => $string('Persian lead, about 140–160 characters.'),
                                'seo_title' => $string('Persian SEO title, at most 60 characters.'),
                                'seo_description' => $string('Persian meta description, 140–155 characters.'),
                                'content_html' => $string('Article body as semantic HTML, starting below the headline (no h1).'),
                                'sources' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'additionalProperties' => false,
                                        'required' => ['title', 'url'],
                                        'properties' => [
                                            'title' => $string('Source page title.'),
                                            'url' => $string('Exact URL from the search results.'),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    protected function submittedArticles(BetaMessage $response): array
    {
        foreach ($response->content as $block) {
            if ($block->type === 'tool_use' && $block->name === self::TOOL) {
                return array_values(array_filter((array) ($block->input['articles'] ?? []), 'is_array'));
            }
        }

        return [];
    }

    /** @return list<string> */
    protected function searchedUrls(BetaMessage $response): array
    {
        $urls = [];

        foreach ($response->content as $block) {
            // A successful search carries a list of results; an error is a single object.
            if ($block->type === 'web_search_tool_result' && is_array($block->content)) {
                foreach ($block->content as $result) {
                    if (isset($result->url)) {
                        $urls[] = $result->url;
                    }
                }
            }
        }

        return $urls;
    }

    protected function client(): Client
    {
        $key = (string) config('services.anthropic.key');

        if (trim($key) === '') {
            throw new RuntimeException('ANTHROPIC_API_KEY is not set.');
        }

        return $this->client ??= new Client(
            apiKey: $key,
            baseUrl: config('services.anthropic.base_url') ?: null,
            requestOptions: ['timeout' => (float) config('news.timeout'), 'maxRetries' => 2],
        );
    }
}
