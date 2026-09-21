<?php

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
    | Classes implementing App\Support\Seo\Sitemap\SitemapSource. Each returns
    | the URLs it owns; later phases add sources for managed pages, articles
    | and company/project pages. See docs/SEO.md.
    |
    */

    'sitemap_sources' => [
        StaticPagesSource::class,
    ],

    'sitemap_cache_seconds' => 3600,

];
