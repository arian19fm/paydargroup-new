<?php

namespace Tests\Unit;

use App\Support\Localization\Jalali;
use App\Support\Localization\PersianNumbers;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class LocalizationHelpersTest extends TestCase
{
    public function test_persian_digit_conversion_round_trips(): void
    {
        $this->assertSame('۰۲۱-۹۱۲۰۰۸۲۸', PersianNumbers::toPersian('021-91200828'));
        $this->assertSame('021-91200828', PersianNumbers::toLatin('۰۲۱-۹۱۲۰۰۸۲۸'));
        $this->assertSame('0912', PersianNumbers::toLatin('٠٩١٢'), 'Arabic-Indic digits are normalised too.');
    }

    public function test_jalali_conversion_matches_known_dates(): void
    {
        $this->assertSame([1405, 6, 18], Jalali::fromGregorian(2026, 9, 9));
        $this->assertSame([1405, 1, 1], Jalali::fromGregorian(2026, 3, 21));
        $this->assertSame([1404, 12, 29], Jalali::fromGregorian(2026, 3, 20));
        $this->assertSame([1403, 12, 30], Jalali::fromGregorian(2025, 3, 20), 'Leap year 1403 has 30 Esfand.');
        $this->assertSame('۱۸ شهریور ۱۴۰۵', Jalali::format(new DateTimeImmutable('2026-09-09')));
        $this->assertSame(1405, Jalali::year(new DateTimeImmutable('2026-09-09')));
    }
}
