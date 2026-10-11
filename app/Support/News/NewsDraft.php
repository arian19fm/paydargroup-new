<?php

namespace App\Support\News;

/** One article as returned by the writer, before it is validated and saved. */
final class NewsDraft
{
    public function __construct(
        public readonly string $title,
        public readonly string $slug,
        public readonly string $excerpt,
        public readonly string $seoTitle,
        public readonly string $seoDescription,
        public readonly string $contentHtml,
        /** @var list<array{title: string, url: string}> */
        public readonly array $sources,
    ) {}

    /** @param  array<string, mixed>  $input  one item of the submit_articles tool input */
    public static function fromToolInput(array $input): self
    {
        $sources = [];
        foreach ((array) ($input['sources'] ?? []) as $source) {
            if (is_array($source) && isset($source['url'])) {
                $sources[] = ['title' => trim((string) ($source['title'] ?? '')), 'url' => trim((string) $source['url'])];
            }
        }

        return new self(
            title: trim((string) ($input['title'] ?? '')),
            slug: trim((string) ($input['slug'] ?? '')),
            excerpt: trim((string) ($input['excerpt'] ?? '')),
            seoTitle: trim((string) ($input['seo_title'] ?? '')),
            seoDescription: trim((string) ($input['seo_description'] ?? '')),
            contentHtml: trim((string) ($input['content_html'] ?? '')),
            sources: $sources,
        );
    }
}
