<?php

use App\Support\Seo\SeoManager;
use App\Support\Settings\Settings;

if (! function_exists('seo')) {
    /**
     * The request-scoped SEO manager. Set page metadata from controllers or
     * views: seo()->title('...')->description('...').
     */
    function seo(): SeoManager
    {
        return app(SeoManager::class);
    }
}

if (! function_exists('settings')) {
    /**
     * Read a site setting ("group.key") with a default, or get the service.
     */
    function settings(?string $key = null, mixed $default = null): mixed
    {
        $settings = app(Settings::class);

        return $key === null ? $settings : $settings->get($key, $default);
    }
}
