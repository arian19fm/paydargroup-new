<?php

namespace App\Support\Localization;

class Direction
{
    /**
     * Text direction ("rtl" or "ltr") for the given locale, defaulting to the
     * current application locale.
     */
    public static function for(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $language = strtolower(explode('_', str_replace('-', '_', $locale))[0]);

        return in_array($language, config('site.rtl_locales', []), true) ? 'rtl' : 'ltr';
    }

    /**
     * BCP 47 language tag for the <html lang> attribute (fa_IR → fa-IR).
     */
    public static function htmlLang(?string $locale = null): string
    {
        return str_replace('_', '-', $locale ?? app()->getLocale());
    }
}
