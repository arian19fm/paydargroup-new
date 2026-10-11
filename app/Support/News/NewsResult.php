<?php

namespace App\Support\News;

/** What one writer call produced: the drafts plus what it cost. */
final class NewsResult
{
    public function __construct(
        /** @var list<NewsDraft> */
        public readonly array $drafts,
        /** @var list<string> URLs that appeared in the run's search results */
        public readonly array $searchedUrls,
        public readonly string $model,
        public readonly int $inputTokens,
        public readonly int $outputTokens,
        public readonly int $webSearches,
    ) {}
}
