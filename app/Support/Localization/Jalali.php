<?php

namespace App\Support\Localization;

use DateTimeInterface;

/**
 * Gregorian → Jalali (Solar Hijri) conversion for display. Dates are stored
 * as Gregorian timestamps; only the rendered text is Persian. The algorithm
 * is the standard integer conversion (Borkowski), valid for 1900–2100.
 */
class Jalali
{
    private const MONTHS = [
        1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند',
    ];

    /** @return array{0: int, 1: int, 2: int} [year, month, day] */
    public static function fromGregorian(int $gy, int $gm, int $gd): array
    {
        $gDaysInMonth = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

        $gy2 = $gm > 2 ? $gy + 1 : $gy;
        $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400) + $gd + $gDaysInMonth[$gm - 1];

        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;

        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }

    /** "۱۸ شهریور ۱۴۰۵" — day, month name, year with Persian digits. */
    public static function format(DateTimeInterface $date): string
    {
        [$jy, $jm, $jd] = self::fromGregorian((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j'));

        return PersianNumbers::toPersian($jd).' '.self::MONTHS[$jm].' '.PersianNumbers::toPersian($jy);
    }

    public static function year(DateTimeInterface $date): int
    {
        return self::fromGregorian((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j'))[0];
    }
}
