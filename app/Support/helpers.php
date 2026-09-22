<?php

use App\Support\Localization\PersianNumbers;
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

if (! function_exists('fa_digits')) {
    /**
     * Render a number or numeric string with Persian digits (display only).
     */
    function fa_digits(string|int|float $value): string
    {
        return PersianNumbers::toPersian($value);
    }
}
