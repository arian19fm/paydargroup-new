# SEO

SEO is an architectural requirement of this site. Everything below is
implemented once, centrally, and reused by every page. Do not hand-write
`<title>`/`<meta>` tags in individual views.

## 1. Components

| Piece | Location | Role |
|---|---|---|
| `SeoManager` | `app/Support/Seo/SeoManager.php` | Request-scoped holder of title, description, canonical, robots, OG type/image, hreflang, breadcrumbs, JSON-LD. `seo()` helper returns it. |
| `JsonLd` | `app/Support/Seo/JsonLd.php` | Builders for Organization, WebSite, WebPage, BreadcrumbList, Article + safe `encode()`. |
| `<x-seo.head />` | `resources/views/components/seo/head.blade.php` | Renders the complete `<head>` block from `SeoManager`. Included by `layouts/site.blade.php`. |
| `<x-seo.json-ld :data />` | `resources/views/components/seo/json-ld.blade.php` | One `<script type="application/ld+json">`. |
| `<x-ui.breadcrumb />` | `resources/views/components/ui/breadcrumb.blade.php` | Visible trail from the same data as the BreadcrumbList schema. |
| `SetRobotsHeader` | `app/Http/Middleware/SetRobotsHeader.php` | `X-Robots-Tag: noindex, nofollow` on every response when not indexable. |
| `RobotsController` | `/robots.txt` | Environment-aware robots.txt. |
| `SitemapController` + `Sitemap\*` | `/sitemap.xml` | Dynamic sitemap from registered sources. |
| `config/seo.php`, `config/site.php` | | Defaults, indexability, identity. |

### Setting page metadata

Preferred — in the controller, where the data is:

```php
public function show(Article $article): View
{
    seo()
        ->title($article->title)
        ->description($article->excerpt)
        ->ogType('article')
        ->image($article->cover_url, 1200, 630, $article->cover_alt)
        ->breadcrumbs([
            ['label' => __('nav.home'), 'url' => route('home')],
            ['label' => __('nav.news'), 'url' => route('news.index')],
            ['label' => $article->title],
        ])
        ->jsonLd(JsonLd::article([...]));

    return view('site.news.show', compact('article'));
}
```

Also possible at the top of a view (runs before the layout renders):

```blade
@extends('layouts.site')
@php seo()->title(__('pages.contact.title'))->description(__('pages.contact.description')); @endphp
```

Managed content (later phases) will carry its own SEO fields (`meta_title`,
`meta_description`, `canonical_url`, `robots`, `og_*`) via a polymorphic
`seo_meta` relation; controllers feed those into `seo()` with fallbacks to
the content itself.

## 2. Title strategy

- Format: `{page title} | {site name}` (`config('seo.title_separator')`).
- Home page: `{site name}` or `{site name} | {tagline}` when `SITE_TAGLINE`
  is set. No page sets a title that repeats the site name.
- Unique per page; ≤ 60 characters ideally; the H1 and the title describe
  the same subject but need not be identical.
- Error pages: `{error title} | {site name}`, `noindex`.

## 3. Descriptions

- `seo()->description()` strips tags, collapses whitespace and limits to 300
  characters (target 120–160 for display).
- Site default: `SEO_DEFAULT_DESCRIPTION` in `.env` (null → tag omitted;
  never emit placeholder text).
- Same text is reused for `og:description` and `twitter:description`.

## 4. Canonical URLs

- Always emitted. Built from **`APP_URL` + request path**, never the request
  host, so `www`/non-`www` and `http`/`https` variants collapse. `APP_URL`
  must be the exact public origin in production.
- Query strings are dropped, except keys whitelisted with
  `seo()->canonicalQuery(['page'])`; `page=1` canonicalises to the base URL.
- Override with `seo()->canonical($url)` (e.g. syndicated content).
- No trailing slashes; lowercase paths (see §9).

## 5. Robots / indexability

- `config('seo.indexable')` is `true` **only in production** unless
  `SEO_INDEXABLE` says otherwise. Local/staging can never be indexed by
  accident: the meta tag says `noindex`, the middleware adds
  `X-Robots-Tag: noindex, nofollow`, and `/robots.txt` disallows `/`.
- Per page: `seo()->noindex()` / `->nofollow()` (search results, filtered
  listings, thank-you pages, error pages).
- `/robots.txt` (production): `Allow: /`, `Disallow: /admin`, `Sitemap:` line.
  It never blocks `/build`, `/storage` or other assets search engines need
  for rendering.

## 6. Open Graph & Twitter

Emitted for every public page: `og:type` (website by default, `article` on
articles), `og:site_name`, `og:locale` (`fa_IR`), `og:title`, `og:description`,
`og:url` (= canonical), and `og:image` with width/height/alt when an image
is set (page image or `SEO_DEFAULT_IMAGE`, 1200×630). Twitter uses
`summary_large_image` when an image exists, otherwise `summary`;
`twitter:site` from `SEO_TWITTER_SITE`.

## 7. Structured data (JSON-LD)

- Encoded with `JsonLd::encode()` — `json_encode` with `JSON_HEX_TAG`,
  `JSON_HEX_AMP`, unescaped Unicode/slashes and `JSON_THROW_ON_ERROR`. No
  string concatenation; `</script>` in data cannot break out.
- Empty/null properties are stripped (`JsonLd::clean`) so nothing invented
  is published.
- **Global** (every public page, `config/seo.php → json_ld`): `Organization`
  (`name`, `url`, optional `logo`, `sameAs`) and `WebSite`.
- **Conditional**: `BreadcrumbList` only when `seo()->breadcrumbs()` was set;
  `Article`/`NewsArticle` only on article pages via `JsonLd::article()`;
  `WebPage` when a page wants it. Future: `Service`, `FAQPage`, `Event` as
  content types appear.

## 8. Headings & semantic HTML

- Exactly one `<h1>` per page, describing the page subject.
- H2–H6 follow the content outline without skipping levels;
  `<x-layout.section heading>` enforces H2+ for sections.
- Never use heading tags for visual size; use `.h*`/`.fs-*` classes.
- Landmarks (`header`, `nav`, `main`, `footer`), `article`/`section` used
  by meaning; lists for navigation; `<time datetime>` for dates.

## 9. URL principles

- Lowercase, hyphen-separated, ASCII or properly encoded Persian slugs
  (decide per content type; be consistent), no file extensions, no trailing
  slash, no query-string-driven pages.
- Stable: slugs are stored with the content; changing one creates a 301 from
  the old URL (redirects table — later phase).
- Hierarchy mirrors breadcrumbs: `/news/{slug}`, `/services/{slug}`.
- Pagination: `?page=N`, self-canonical per page, `page=1` → base URL; add
  `noindex` for filtered/sorted variants.
- Only real routes appear in navigation, sitemaps and breadcrumbs.

## 10. Sitemap plan

`/sitemap.xml` is generated by `App\Support\Seo\Sitemap\SitemapBuilder` from
the sources listed in `config/seo.php → sitemap_sources`, cached for
`sitemap_cache_seconds`, written with `XMLWriter`.

| Source | Status | URLs | lastmod |
|---|---|---|---|
| `StaticPagesSource` | implemented | named static routes (`home`, later `about`, `contact`, …) | – |
| managed pages | planned | published CMS pages | `updated_at` |
| articles / news | planned | published articles (+ category listings, page 1 only) | `updated_at` |
| company / project pages | planned | future project or company entity pages | `updated_at` |

Rules: only canonical, indexable, published URLs; absolute URLs under
`APP_URL`; invalidate the cache on publish; switch to a sitemap index with
one file per source once any source exceeds ~10k URLs; images may be added
via the image sitemap extension when the media library exists.

## 11. Image SEO

- Meaningful `alt` (required admin field; empty `alt=""` for decorative).
- Explicit `width`/`height`; responsive `srcset`/`sizes`; WebP/AVIF.
- Descriptive filenames; captions where they add information.
- OG image 1200×630; LCP image not lazy-loaded.

## 12. Core Web Vitals principles

- **LCP**: server-rendered HTML, critical content first, no third-party
  blocking requests, self-hosted fonts with `swap`, eager LCP image, cached
  pages.
- **CLS**: image dimensions always set, fonts with metric-compatible
  fallbacks, no late-injected banners, reserved space for widgets.
- **INP**: tiny JS bundle, Bootstrap modules on demand, Vue only where used,
  no long tasks on load.
- Measure with Lighthouse/PageSpeed on mobile for every template before
  release; budgets in `FRONTEND.md` §11.

## 13. hreflang (future multilingual)

`seo()->alternates(['fa' => $faUrl, 'en' => $enUrl])` emits
`<link rel="alternate" hreflang>` for each locale plus `x-default`. Nothing
is emitted for single-language pages. When a second language is added, each
translated page must list *all* its variants (including itself).

## 14. 301 redirects (future)

A `redirects` table (`from_path`, `to_url`, `status_code`) applied by
middleware before routing, editable in the admin, with automatic entries on
slug changes. Documented here so URL changes are never silent.
