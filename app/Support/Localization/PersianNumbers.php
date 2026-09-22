<?php

namespace App\Support\Localization;

/**
 * Digit shaping for Persian output. The design renders every number with
 * Persian-Arabic digits (۰–۹); converting at render time keeps the stored
 * data ASCII (sortable, machine-readable).
 */
class PersianNumbers
{
    private const LATIN = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    private const PERSIAN = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    public static function toPersian(string|int|float $value): string
    {
        return str_replace(self::LATIN, self::PERSIAN, (string) $value);
    }

    public static function toLatin(string $value): string
    {
        // Accept Arabic-Indic digits too (common on some keyboards).
        $value = str_replace(['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'], self::LATIN, $value);

        return str_replace(self::PERSIAN, self::LATIN, $value);
    }
}
