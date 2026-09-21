<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site identity
    |--------------------------------------------------------------------------
    |
    | Public-facing identity used by layouts, SEO defaults and JSON-LD. Only
    | values that are genuinely known belong here; anything else stays null
    | and is simply omitted from the output (never invent company data).
    |
    */

    'name' => env('APP_NAME', 'Paydar Group'),

    // Short tagline appended to the home page title when set (null = none).
    'tagline' => env('SITE_TAGLINE'),

    // Absolute URL of the organisation logo for JSON-LD / OG (null = omitted).
    'logo' => env('SITE_LOGO_URL'),

    // Social profile URLs for Organization.sameAs (empty = omitted).
    'same_as' => array_values(array_filter(explode(',', (string) env('SITE_SAME_AS', '')))),

    /*
    |--------------------------------------------------------------------------
    | Icons
    |--------------------------------------------------------------------------
    |
    | <link> tags emitted by <x-seo.head>. Add an SVG icon, apple-touch-icon
    | and web manifest here once the brand assets exist (all self-hosted
    | under public/).
    |
    */

    'icons' => [
        ['rel' => 'icon', 'href' => '/favicon.ico', 'sizes' => 'any'],
        // ['rel' => 'icon', 'href' => '/icon.svg', 'type' => 'image/svg+xml'],
        // ['rel' => 'apple-touch-icon', 'href' => '/apple-touch-icon.png'],
        // ['rel' => 'manifest', 'href' => '/site.webmanifest'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Localisation
    |--------------------------------------------------------------------------
    */

    // Locales rendered right-to-left; everything else is LTR.
    'rtl_locales' => ['fa', 'ar', 'he', 'ur'],

    // Open Graph locale for the default app locale.
    'og_locale' => env('SITE_OG_LOCALE', 'fa_IR'),

    /*
    |--------------------------------------------------------------------------
    | Primary navigation
    |--------------------------------------------------------------------------
    |
    | Temporary static navigation until menus are managed in the admin panel.
    | Each item: label (translation key or text), route name, optional
    | children. Only real routes may be listed.
    |
    */

    'navigation' => [
        ['label' => 'nav.home', 'route' => 'home'],
    ],

];
