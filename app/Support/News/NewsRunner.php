<?php

namespace App\Support\News;

use App\Models\Article;
use App\Models\NewsRun;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Decides when automatic news runs and executes runs. Driven by the
 * `news:tick` command every minute (routes/console.php):
 *
 * - a run left "running" too long (the process died) is marked failed;
 * - a run requested from the admin ("اجرا همین حالا") is executed first;
 * - otherwise, when enabled, the day's scheduled run starts once the
 *   configured time has passed (so a missed minute still runs that day).
 */
class NewsRunner
{
    public const TICK_CACHE_KEY = 'news.last_tick';

    public function __construct(protected NewsWriter $writer, protected NewsPublisher $publisher) {}

    public function tick(): ?NewsRun
    {
        cache()->forever(self::TICK_CACHE_KEY, now()->toIso8601String());

        NewsRun::query()->where('status', NewsRun::RUNNING)
            ->where('started_at', '<', now()->subMinutes((int) config('news.stale_after_minutes')))
            ->update(['status' => NewsRun::FAILED, 'finished_at' => now(), 'error' => 'Timed out (the process stopped).']);

        if (NewsRun::query()->where('status', NewsRun::RUNNING)->exists()) {
            return null;
        }

        $run = NewsRun::query()->where('status', NewsRun::QUEUED)->oldest('id')->first()
            ?? $this->dueScheduledRun();

        return $run ? $this->execute($run) : null;
    }

    public function execute(NewsRun $run): NewsRun
    {
        $settings = NewsSettings::load();
        $run->update(['status' => NewsRun::RUNNING, 'started_at' => now(), 'model' => config('news.model'), 'error' => null]);

        try {
            $result = $this->writer->write(
                $settings->articlesPerRun,
                $settings->topics,
                $this->recentTitles(),
                now(config('news.timezone')),
            );

            $articles = $this->publisher->publish($run, $result->drafts, $result->searchedUrls, $settings);

            $run->update([
                'status' => $articles === [] ? NewsRun::FAILED : NewsRun::SUCCEEDED,
                'finished_at' => now(),
                'articles_count' => count($articles),
                'model' => $result->model,
                'input_tokens' => $result->inputTokens,
                'output_tokens' => $result->outputTokens,
                'web_searches' => $result->webSearches,
                'error' => $articles === [] ? 'No usable article was returned (missing body or verifiable sources).' : null,
            ]);
        } catch (Throwable $e) {
            Log::error('Automatic news run failed', ['run' => $run->id, 'exception' => $e]);
            $run->update(['status' => NewsRun::FAILED, 'finished_at' => now(), 'error' => class_basename($e).': '.mb_substr($e->getMessage(), 0, 2000)]);
        }

        return $run->refresh();
    }

    protected function dueScheduledRun(): ?NewsRun
    {
        $settings = NewsSettings::load();

        if (! $settings->enabled || ! NewsSettings::hasApiKey()) {
            return null;
        }

        $now = now(config('news.timezone'));
        [$hour, $minute] = array_map('intval', explode(':', $settings->runAt));

        if ($now->lt($now->copy()->setTime($hour, $minute))) {
            return null;
        }

        $ranToday = NewsRun::query()->where('trigger', 'scheduled')
            ->where('created_at', '>=', $now->copy()->startOfDay()->utc())
            ->exists();

        return $ranToday ? null : NewsRun::create(['trigger' => 'scheduled', 'status' => NewsRun::QUEUED]);
    }

    /** @return list<string> */
    protected function recentTitles(): array
    {
        return Article::query()->where('created_at', '>=', now()->subDays(7))
            ->latest('id')->limit(40)->pluck('title')->all();
    }
}
