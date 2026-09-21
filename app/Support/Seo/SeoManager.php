<?php

namespace App\Support\Seo;

use App\Contracts\Seoable;
use App\Models\Media;
use Illuminate\Support\Str;

/**
 * Per-request holder of everything the <head> needs for SEO.
 *
 * Controllers (preferred) or views set values through the fluent setters;
 * the <x-seo.head> component reads them. Every value has a sensible default
 * derived from config so a page that sets nothing still emits a complete,
 * valid head.
 *
 *     seo()->title('درباره ما')->description('...')->breadcrumbs([...]);
 */
class SeoManager
{
    protected ?string $title = null;

    protected ?string $description = null;

    protected ?string $canonical = null;

    /** @var list<string> query parameters preserved in the canonical URL */
    protected array $canonicalQuery = [];

    protected bool $noindex = false;

    protected bool $nofollow = false;

    protected string $ogType = 'website';

    protected ?array $image = null;

    /** @var array<string, string> hreflang => absolute URL */
    protected array $alternates = [];

    /** @var list<array{label: string, url: ?string}> */
    protected array $breadcrumbs = [];

    /** @var list<array<string, mixed>> */
    protected array $jsonLd = [];

    // ------------------------------------------------------------------ title

    public function title(?string $title): static
    {
        $this->title = $title !== null ? trim($title) : null;

        return $this;
    }

    /** The page-specific title without the site name (null on the home page). */
    public function rawTitle(): ?string
    {
        return $this->title !== '' ? $this->title : null;
    }

    /** The full <title> text: "{page}{sep}{site}" or the site name (+ tagline). */
    public function fullTitle(): string
    {
        $site = self::siteName();
        $separator = settings('seo.title_separator', config('seo.title_separator'));

        if ($this->rawTitle() === null) {
            $default = settings('seo.default_title');
            $tagline = settings('general.site_tagline', config('site.tagline'));

            return $default ?: ($tagline ? $site.$separator.$tagline : $site);
        }

        return $this->rawTitle().$separator.$site;
    }

    /** Site name from settings, falling back to config/site.php. */
    public static function siteName(): string
    {
        return settings('general.site_name', config('site.name'));
    }

    // ------------------------------------------------------------ description

    public function description(?string $description): static
    {
        $this->description = $description !== null
            ? Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($description))), 300, '')
            : null;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description ?? settings('seo.default_description', config('seo.default_description'));
    }

    // -------------------------------------------------------------- canonical

    /** Explicit canonical URL (absolute). Null restores the derived default. */
    public function canonical(?string $url): static
    {
        $this->canonical = $url;

        return $this;
    }

    /**
     * Keep the given query parameters in the derived canonical, e.g.
     * ['page'] so paginated listings canonicalise to themselves.
     *
     * @param  list<string>  $keys
     */
    public function canonicalQuery(array $keys): static
    {
        $this->canonicalQuery = $keys;

        return $this;
    }

    /**
     * The canonical is built from APP_URL + the request path (never the
     * request host) so host/scheme variants collapse to one URL. Query
     * strings are dropped unless whitelisted via canonicalQuery().
     */
    public function getCanonical(): string
    {
        if ($this->canonical !== null) {
            return $this->canonical;
        }

        $request = request();
        $url = self::absoluteUrl($request->path());

        if ($this->canonicalQuery !== []) {
            $query = array_filter(
                $request->only($this->canonicalQuery),
                fn ($value) => $value !== null && $value !== '' && $value !== '1' && $value !== 1
            );

            if ($query !== []) {
                ksort($query);
                $url .= '?'.http_build_query($query);
            }
        }

        return $url;
    }

    // ----------------------------------------------------------------- robots

    public function noindex(bool $noindex = true): static
    {
        $this->noindex = $noindex;

        return $this;
    }

    public function nofollow(bool $nofollow = true): static
    {
        $this->nofollow = $nofollow;

        return $this;
    }

    public function isIndexable(): bool
    {
        return config('seo.indexable') && ! $this->noindex;
    }

    /** Content for <meta name="robots">. */
    public function robots(): string
    {
        $index = $this->isIndexable() ? 'index' : 'noindex';
        $follow = $this->nofollow ? 'nofollow' : 'follow';

        return $index.', '.$follow;
    }

    // ---------------------------------------------------------- social / image

    /** Open Graph object type: website (default), article, profile, ... */
    public function ogType(string $type): static
    {
        $this->ogType = $type;

        return $this;
    }

    public function getOgType(): string
    {
        return $this->ogType;
    }

    /** Sharing image (absolute URL). Dimensions and alt improve previews. */
    public function image(?string $url, ?int $width = null, ?int $height = null, ?string $alt = null): static
    {
        $this->image = $url ? array_filter([
            'url' => $url,
            'width' => $width,
            'height' => $height,
            'alt' => $alt,
        ]) : null;

        return $this;
    }

    /** @return array{url: string, width?: int, height?: int, alt?: string}|null */
    public function getImage(): ?array
    {
        if ($this->image) {
            return $this->image;
        }

        if ($mediaId = settings('seo.default_og_image_id')) {
            $media = Media::find($mediaId);

            if ($media) {
                return self::imageFromMedia($media);
            }
        }

        $default = config('seo.default_image');

        return $default ? [
            'url' => $default,
            'width' => config('seo.default_image_width'),
            'height' => config('seo.default_image_height'),
        ] : null;
    }

    /** @return array{url: string, width?: int, height?: int, alt?: string} */
    public static function imageFromMedia(Media $media): array
    {
        return array_filter([
            'url' => $media->url(),
            'width' => $media->width,
            'height' => $media->height,
            'alt' => $media->alt_text,
        ]);
    }

    // ------------------------------------------------------------- from model

    /**
     * Fill the head from a managed content item and its stored SEO
     * overrides. Precedence for each field: explicit override in seo_meta →
     * the model's own fallback → site default. Nothing is written back.
     */
    public function fromModel(Seoable $model): static
    {
        $meta = $model->seoMeta();

        $this->title($meta?->title ?: $model->seoDefaultTitle());
        $this->description($meta?->description ?: $model->seoDefaultDescription());
        $this->canonical($meta?->canonical_url ?: $model->publicUrl());
        $this->ogType($model->seoOgType());

        if ($meta) {
            $this->noindex(! $meta->robots_index);
            $this->nofollow(! $meta->robots_follow);
            $this->ogOverrides($meta->og_title, $meta->og_description);
            $this->twitterOverrides($meta->twitter_title, $meta->twitter_description);
        }

        $image = $meta?->ogImage ?? $model->seoDefaultImage();

        if ($image) {
            $this->image($image->url(), $image->width, $image->height, $image->alt_text);
        }

        if ($meta?->twitterImage) {
            $this->twitterImage(self::imageFromMedia($meta->twitterImage));
        }

        return $this;
    }

    // ----------------------------------------------------- social overrides

    protected ?string $ogTitle = null;

    protected ?string $ogDescription = null;

    protected ?string $twitterTitle = null;

    protected ?string $twitterDescription = null;

    /** @var array{url: string, width?: int, height?: int, alt?: string}|null */
    protected ?array $twitterImage = null;

    public function ogOverrides(?string $title, ?string $description): static
    {
        $this->ogTitle = $title ?: null;
        $this->ogDescription = $description ?: null;

        return $this;
    }

    public function twitterOverrides(?string $title, ?string $description): static
    {
        $this->twitterTitle = $title ?: null;
        $this->twitterDescription = $description ?: null;

        return $this;
    }

    public function twitterImage(?array $image): static
    {
        $this->twitterImage = $image;

        return $this;
    }

    public function getOgTitle(): string
    {
        return $this->ogTitle ?? $this->rawTitle() ?? $this->fullTitle();
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription ?? $this->getDescription();
    }

    public function getTwitterTitle(): string
    {
        return $this->twitterTitle ?? $this->getOgTitle();
    }

    public function getTwitterDescription(): ?string
    {
        return $this->twitterDescription ?? $this->getOgDescription();
    }

    /** @return array{url: string, width?: int, height?: int, alt?: string}|null */
    public function getTwitterImage(): ?array
    {
        return $this->twitterImage ?? $this->getImage();
    }

    public function getTwitterSite(): ?string
    {
        return settings('seo.twitter_site', config('seo.twitter_site'));
    }

    // --------------------------------------------------------------- hreflang

    /**
     * Alternate-language versions of this page. An "x-default" entry is added
     * automatically pointing at the current locale's URL.
     *
     * @param  array<string, string>  $alternates  hreflang => absolute URL
     */
    public function alternates(array $alternates): static
    {
        $this->alternates = $alternates;

        return $this;
    }

    /** @return array<string, string> */
    public function getAlternates(): array
    {
        if ($this->alternates === []) {
            return [];
        }

        return $this->alternates + ['x-default' => $this->alternates[app()->getLocale()] ?? $this->getCanonical()];
    }

    // ------------------------------------------------------------ breadcrumbs

    /**
     * Breadcrumb trail, home first. The last item is the current page and
     * may omit the URL. Rendered as HTML by <x-ui.breadcrumb> and as
     * BreadcrumbList JSON-LD from the same data.
     *
     * @param  list<array{label: string, url?: ?string}>  $items
     */
    public function breadcrumbs(array $items): static
    {
        $this->breadcrumbs = array_map(fn (array $item) => [
            'label' => $item['label'],
            'url' => $item['url'] ?? null,
        ], array_values($items));

        return $this;
    }

    /** @return list<array{label: string, url: ?string}> */
    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }

    // ---------------------------------------------------------------- JSON-LD

    /**
     * Add a schema.org object for this page (an associative array; "@context"
     * is added when missing). Use the JsonLd builders for common types.
     *
     * @param  array<string, mixed>  $schema
     */
    public function jsonLd(array $schema): static
    {
        $this->jsonLd[] = ['@context' => 'https://schema.org'] + $schema;

        return $this;
    }

    /**
     * All JSON-LD objects to emit: global Organization/WebSite (if enabled),
     * BreadcrumbList (if breadcrumbs exist) and page-specific schemas.
     *
     * @return list<array<string, mixed>>
     */
    public function jsonLdObjects(): array
    {
        $objects = [];

        if (config('seo.json_ld.organization')) {
            $objects[] = JsonLd::organization();
        }

        if (config('seo.json_ld.website')) {
            $objects[] = JsonLd::website();
        }

        if ($this->breadcrumbs !== []) {
            $objects[] = JsonLd::breadcrumbList($this->breadcrumbs);
        }

        return array_merge($objects, $this->jsonLd);
    }

    // ---------------------------------------------------------------- helpers

    /** Absolute URL under APP_URL for a path (no trailing slash, "/" → root). */
    public static function absoluteUrl(string $path = ''): string
    {
        $base = rtrim(config('app.url'), '/');
        $path = trim($path, '/');

        return $path === '' ? $base : $base.'/'.$path;
    }
}
