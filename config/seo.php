<?php

use App\Support\Seo\Sitemap\ArticleCategoriesSource;
use App\Support\Seo\Sitemap\ArticlesSource;
use App\Support\Seo\Sitemap\BusinessesSource;
use App\Support\Seo\Sitemap\PagesSource;
use App\Support\Seo\Sitemap\StaticPagesSource;

return [

    /*
    |--------------------------------------------------------------------------
    | Indexability
    |--------------------------------------------------------------------------
    |
    | When false, every response carries `X-Robots-Tag: noindex, nofollow`,
    | the robots meta tag says noindex and robots.txt disallows everything.
    | Defaults to true ONLY in production so staging/local can never be
    | indexed by accident. Override with SEO_INDEXABLE in .env.
    |
    */

    'indexable' => (bool) env('SEO_INDEXABLE', env('APP_ENV') === 'production'),

    /*
    |--------------------------------------------------------------------------
    | Title strategy
    |--------------------------------------------------------------------------
    |
    | Page titles render as "{page title}{separator}{site name}". The home
    | page (no page title) renders the site name, plus the tagline when set.
    |
    */

    'title_separator' => ' | ',

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    |
    | Site-wide fallbacks used when a page does not set its own value. Leave
    | null to omit the tag rather than emitting placeholder text.
    |
    */

    'default_description' => env('SEO_DEFAULT_DESCRIPTION'),

    // Absolute URL of a default social sharing image (1200×630 recommended).
    'default_image' => env('SEO_DEFAULT_IMAGE'),
    'default_image_width' => 1200,
    'default_image_height' => 630,

    // Twitter/X handle (e.g. "@paydargroup") for twitter:site; null = omitted.
    'twitter_site' => env('SEO_TWITTER_SITE'),

    /*
    |--------------------------------------------------------------------------
    | Structured data
    |--------------------------------------------------------------------------
    */

    'json_ld' => [
        // Emit Organization + WebSite on every public page.
        'organization' => true,
        'website' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap
    |--------------------------------------------------------------------------
    |
    | /sitemap.xml is an index of one sitemap per section below
    | (/sitemaps/{name}.xml), each built from classes implementing
    | App\Support\Seo\Sitemap\SitemapSource, plus /sitemap-news.xml for the
    | last two days of news. Only published, indexable, canonical content is
    | emitted. See docs/SEO.md §10.
    |
    */

    'sitemaps' => [
        'pages' => [
            StaticPagesSource::class,
            PagesSource::class,
            BusinessesSource::class,
        ],
        'articles' => [
            ArticlesSource::class,
            ArticleCategoriesSource::class,
        ],
    ],

    // <news:language> of the news sitemap (ISO 639).
    'news_language' => 'fa',

    // Cache key prefix: "<prefix>.index", "<prefix>.<section>", "<prefix>.news".
    'sitemap_cache_key' => 'seo.sitemap',

    'sitemap_cache_seconds' => 3600,

];
