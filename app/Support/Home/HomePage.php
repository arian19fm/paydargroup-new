<?php

namespace App\Support\Home;

use App\Models\Article;
use App\Models\Media;
use App\Models\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;

/**
 * Assembles the data the public home page renders: the product line-up
 * (config + copy), the latest published articles and the CMS page links
 * behind the calls to action. Purely read-only; nothing here is cached
 * beyond what the underlying repositories cache.
 */
class HomePage
{
    /**
     * Product cards in display order.
     *
     * @return list<array{key: string, number: string, image: string, image_type: string, url: ?string, name: string, plain_name: string, description: string, features: list<string>, image_alt: string}>
     */
    public function products(array $pageUrls): array
    {
        return array_map(function (array $product) use ($pageUrls) {
            $copy = __('home.products.items.'.$product['key']);

            return [
                ...$product,
                'url' => $pageUrls[$product['slug']] ?? null,
                'name' => $copy['name'],
                'plain_name' => $copy['plain_name'],
                'description' => $copy['description'],
                'features' => $copy['features'],
                'image_alt' => $copy['image_alt'],
            ];
        }, config('home.products', []));
    }

    /**
     * Latest published articles with what the cards need.
     *
     * @return Collection<int, Article>
     */
    public function latestArticles(): Collection
    {
        try {
            return Article::query()->published()
                ->with(['featuredImage', 'categories' => fn ($q) => $q->active()->ordered()])
                ->orderByDesc('published_at')
                ->limit((int) config('home.articles_limit', 4))
                ->get();
        } catch (QueryException) {
            return new Collection;
        }
    }

    /**
     * Absolute URLs of the published CMS pages behind the home page links,
     * keyed by slug. Unpublished or missing pages are simply absent, so the
     * views render those calls to action without a link.
     *
     * @param  list<string>  $slugs
     * @return array<string, string>
     */
    public function pageUrls(array $slugs): array
    {
        $slugs = array_values(array_unique(array_filter($slugs)));

        if ($slugs === []) {
            return [];
        }

        try {
            return Page::query()->published()->whereIn('slug', $slugs)->pluck('slug')
                ->mapWithKeys(fn (string $slug) => [$slug => route('pages.show', $slug)])
                ->all();
        } catch (QueryException) {
            return [];
        }
    }

    /**
     * Hero background media chosen in Settings → home: [video, image].
     * Each is null unless the referenced media exists and has the right
     * type, so a stale or mistyped ID silently falls back to the design.
     *
     * @return array{0: ?Media, 1: ?Media}
     */
    public function heroMedia(): array
    {
        $ids = array_filter([
            'video' => (int) settings('home.hero_video_media_id'),
            'image' => (int) settings('home.hero_image_media_id'),
        ]);

        if ($ids === []) {
            return [null, null];
        }

        try {
            $media = Media::query()->whereIn('id', $ids)->get()->keyBy('id');
        } catch (QueryException) {
            return [null, null];
        }

        $video = $media->get($ids['video'] ?? 0);
        $image = $media->get($ids['image'] ?? 0);

        return [
            $video?->isVideo() ? $video : null,
            $image?->isImage() ? $image : null,
        ];
    }

    /** Every slug the home page may link to (products + section CTAs). */
    public function linkedSlugs(): array
    {
        return [
            ...array_column(config('home.products', []), 'slug'),
            ...array_values(config('home.links', [])),
        ];
    }
}
