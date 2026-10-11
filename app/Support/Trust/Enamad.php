<?php

namespace App\Support\Trust;

/**
 * Enamad (اینماد) e-commerce trust seal. The admin pastes the snippet
 * Enamad gives (or just its link) into Settings → عمومی; only the seal's
 * `id` and `Code` are taken from it and the markup is rendered by the
 * footer, so pasted HTML is never output as is.
 */
class Enamad
{
    public const HOST = 'https://trustseal.enamad.ir';

    /** @return array{id: string, code: string}|null */
    public static function parse(?string $snippet): ?array
    {
        if ($snippet === null || ! str_contains(strtolower($snippet), 'trustseal.enamad.ir')) {
            return null;
        }

        $snippet = html_entity_decode($snippet, ENT_QUOTES | ENT_HTML5);

        if (! preg_match('/[?&]id=(\d{1,20})(?![\d])/i', $snippet, $id) || ! preg_match('/[?&]code=([A-Za-z0-9]{1,64})(?![A-Za-z0-9])/i', $snippet, $code)) {
            return null;
        }

        return ['id' => $id[1], 'code' => $code[1]];
    }

    /** @return array{id: string, code: string, link: string, logo: string}|null */
    public static function fromSettings(): ?array
    {
        $seal = self::parse(settings('general.enamad_code'));

        if ($seal === null) {
            return null;
        }

        $query = http_build_query(['id' => $seal['id'], 'Code' => $seal['code']]);

        return $seal + ['link' => self::HOST.'/?'.$query, 'logo' => self::HOST.'/logo.aspx?'.$query];
    }
}
