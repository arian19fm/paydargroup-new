<?php

namespace App\Support\News;

use App\Support\Settings\Settings;

/**
 * The editor-facing switches of automatic news, read from the hidden
 * `news` settings group (config/settings.php) with safe defaults.
 */
final class NewsSettings
{
    public function __construct(
        public readonly bool $enabled,
        public readonly string $runAt,
        public readonly int $articlesPerRun,
        public readonly bool $autoPublish,
        /** @var list<string> */
        public readonly array $topics,
        public readonly ?int $categoryId,
        public readonly ?int $authorId,
        public readonly ?int $coverMediaId,
    ) {}

    public static function load(): self
    {
        $s = app(Settings::class);
        $runAt = (string) $s->get('news.run_at', '09:00');
        $topics = array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $s->get('news.topics', '')))));

        return new self(
            enabled: (bool) $s->get('news.enabled', false),
            runAt: preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $runAt) ? $runAt : '09:00',
            articlesPerRun: max(1, min((int) config('news.max_articles_per_run'), (int) $s->get('news.articles_per_run', 1))),
            autoPublish: (bool) $s->get('news.auto_publish', true),
            topics: $topics ?: config('news.default_topics'),
            categoryId: $s->get('news.category_id') ? (int) $s->get('news.category_id') : null,
            authorId: $s->get('news.author_id') ? (int) $s->get('news.author_id') : null,
            coverMediaId: $s->get('news.cover_media_id') ? (int) $s->get('news.cover_media_id') : null,
        );
    }

    public static function hasApiKey(): bool
    {
        return trim((string) config('services.anthropic.key')) !== '';
    }
}
