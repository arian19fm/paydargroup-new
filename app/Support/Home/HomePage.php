<?php

namespace App\Support\Home;

use App\Models\Article;
use App\Models\Business;
use App\Models\Faq;
use App\Models\Media;
use App\Models\Page;
use App\Support\Seo\SeoManager;
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
     * Business cards for the "our products" section (also the footer
     * services column), in display order: the published businesses
     * managed in admin → businesses.
     *
     * @return list<array<string, mixed>>
     */
    public function products(): array
    {
        return $this->businesses()->values()->map(fn (Business $business, int $index) => [
            'key' => $business->accentKey(),
            'number' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
            'media' => $business->image?->isImage() ? $business->image : null,
            'url' => route('businesses.show', $business->slug),
            'name' => $business->title,
            'plain_name' => $business->title,
            'description' => $business->tagline,
            'features' => $business->featureList(),
            'image_alt' => $business->image?->alt_text ?: $business->title,
        ])->all();
    }

    /**
     * Texts and links of every home page section, from Settings → home.
     * A blank text is returned as '' and the view leaves that element out;
     * the H1 falls back to the site name and the submit button to its
     * generic label, since neither can be missing. A blank link falls back
     * to the section's natural target (null when that page is not
     * published, which renders the CTA without a link).
     *
     * @return array<string, array<string, ?string>>
     */
    public function sections(array $links): array
    {
        $text = fn (string $key) => trim((string) settings('home.'.$key));
        $link = fn (string $key, ?string $fallback) => $text($key) ?: $fallback;

        $headline = array_values(array_filter([$text('hero_title_line_1'), $text('hero_title_line_2')]));

        return [
            'hero' => [
                'eyebrow' => $text('hero_eyebrow'),
                'headline' => $headline ?: [SeoManager::siteName()],
                'text' => $text('hero_text'),
                'cta' => $text('hero_cta_label'),
                'cta_url' => $link('hero_cta_url', $links['about'] ?? null),
            ],
            'products' => [
                'eyebrow' => $text('products_eyebrow'),
                'heading_highlight' => $text('products_title_highlight'),
                'heading' => $text('products_title'),
                'text' => $text('products_text'),
                'cta' => $text('products_cta_label'),
                'cta_url' => $link('products_cta_url', $links['products'] ?? null),
                'card_cta' => $text('products_card_cta_label'),
            ],
            'blog' => [
                'eyebrow' => $text('blog_eyebrow'),
                'heading' => $text('blog_title'),
                'heading_highlight' => $text('blog_title_highlight'),
                'text' => $text('blog_text'),
                'cta' => $text('blog_cta_label'),
                'cta_url' => $link('blog_cta_url', $links['blog'] ?? null),
                'empty' => $text('blog_empty_text'),
            ],
            'faq' => [
                'eyebrow' => $text('faq_eyebrow'),
                'heading_highlight' => $text('faq_title_highlight'),
                'heading' => $text('faq_title'),
                'ask_placeholder' => $text('faq_ask_placeholder'),
                'card_title' => $text('faq_card_title'),
                'card_text' => $text('faq_card_text'),
                'card_cta' => $text('faq_card_cta_label'),
                'card_cta_url' => $link('faq_card_cta_url', '#contact'),
            ],
            'contact' => [
                'eyebrow' => $text('contact_eyebrow'),
                'heading' => $text('contact_title'),
                'text' => $text('contact_text'),
                'submit' => $text('contact_submit_label') ?: __('home.contact.submit'),
            ],
        ];
    }

    /** Background photo of the contact section from settings, else null (the designed photo). */
    public function contactImage(): ?Media
    {
        $id = (int) settings('home.contact_image_media_id');

        if ($id <= 0) {
            return null;
        }

        try {
            $media = Media::query()->find($id);
        } catch (QueryException) {
            return null;
        }

        return $media?->isImage() ? $media : null;
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
     * Active questions of the FAQ section in display order, as managed in
     * admin → FAQ (empty when the table does not exist yet).
     *
     * @return list<array{question: string, answer: ?string}>
     */
    public function faqItems(): array
    {
        try {
            return Faq::query()->active()->ordered()->get(['question', 'answer'])
                ->map(fn (Faq $faq) => ['question' => $faq->question, 'answer' => $faq->answer])
                ->all();
        } catch (QueryException) {
            return [];
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

    /** Every CMS page slug the home page section CTAs may link to. */
    public function linkedSlugs(): array
    {
        return array_values(config('home.links', []));
    }
}
