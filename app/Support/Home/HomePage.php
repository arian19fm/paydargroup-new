<?php

namespace App\Support\Home;

use App\Models\Article;
use App\Models\Business;
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
     * Business cards for the "our products" section, in display order.
     * Published businesses from the CMS come first; when none exists yet
     * the designed line-up from config/home.php is rendered so the section
     * never disappears. Every card has the same shape whichever source it
     * came from: `media` (Media|null) or `image`/`image_type` (design asset).
     *
     * @return list<array<string, mixed>>
     */
    public function products(array $pageUrls): array
    {
        $businesses = $this->businesses();

        if ($businesses->isNotEmpty()) {
            return $businesses->values()->map(fn (Business $business, int $index) => [
                'key' => $business->accentKey(),
                'number' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'media' => $business->image?->isImage() ? $business->image : null,
                'image' => null,
                'image_type' => null,
                'url' => route('businesses.show', $business->slug),
                'name' => $business->title,
                'plain_name' => $business->title,
                'description' => $business->tagline,
                'features' => $business->featureList(),
                'image_alt' => $business->image?->alt_text ?: $business->title,
            ])->all();
        }

        return array_map(function (array $product) use ($pageUrls) {
            $copy = __('home.products.items.'.$product['key']);

            return [
                ...$product,
                'media' => null,
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
     * Copy of the "our products" section: Settings → home overrides, the
     * designed text otherwise. The CTA links to the configured URL, else
     * to the `products` CMS page when it is published.
     *
     * @return array{eyebrow: string, heading_highlight: string, heading: string, text: string, cta: string, cta_url: ?string}
     */
    public function productsSection(array $links): array
    {
        $setting = fn (string $key, string $fallback) => trim((string) settings('home.'.$key)) ?: __($fallback);

        return [
            'eyebrow' => $setting('products_eyebrow', 'home.products.eyebrow'),
            'heading_highlight' => $setting('products_title_highlight', 'home.products.heading_highlight'),
            'heading' => $setting('products_title', 'home.products.heading'),
            'text' => $setting('products_text', 'home.products.text'),
            'cta' => $setting('products_cta_label', 'home.products.cta'),
            'cta_url' => trim((string) settings('home.products_cta_url')) ?: ($links['products'] ?? null),
        ];
    }

    /**
     * Published businesses in display order (empty when the table does not
     * exist yet, e.g. before migrations run).
     *
     * @return Collection<int, Business>
     */
    public function businesses(): Collection
    {
        try {
            return Business::query()->published()->ordered()->with('image')->get();
        } catch (QueryException) {
            return new Collection;
        }
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
