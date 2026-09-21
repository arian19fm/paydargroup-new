<?php

namespace App\Support\Seo;

use DateTimeInterface;

/**
 * Builders for the schema.org objects the site uses. Each returns a plain
 * array; encoding happens once, safely, in the <x-seo.json-ld> component.
 * Empty/null properties are stripped so nothing fake is ever emitted.
 */
class JsonLd
{
    /** Site-wide Organization (from config/site.php). */
    public static function organization(): array
    {
        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => SeoManager::absoluteUrl().'#organization',
            'name' => config('site.name'),
            'url' => SeoManager::absoluteUrl(),
            'logo' => config('site.logo'),
            'sameAs' => config('site.same_as', []),
        ]);
    }

    /** Site-wide WebSite. */
    public static function website(): array
    {
        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => SeoManager::absoluteUrl().'#website',
            'name' => config('site.name'),
            'url' => SeoManager::absoluteUrl(),
            'inLanguage' => app()->getLocale(),
            'publisher' => ['@id' => SeoManager::absoluteUrl().'#organization'],
        ]);
    }

    /** WebPage for the current page (call from pages that want it). */
    public static function webPage(string $url, string $name, ?string $description = null): array
    {
        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'url' => $url,
            'name' => $name,
            'description' => $description,
            'inLanguage' => app()->getLocale(),
            'isPartOf' => ['@id' => SeoManager::absoluteUrl().'#website'],
        ]);
    }

    /**
     * BreadcrumbList from the SeoManager breadcrumb format.
     *
     * @param  list<array{label: string, url: ?string}>  $items
     */
    public static function breadcrumbList(array $items): array
    {
        $elements = [];

        foreach (array_values($items) as $index => $item) {
            $elements[] = self::clean([
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
                'item' => $item['url'],
            ]);
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    /**
     * Article / NewsArticle. Only for article pages — never global.
     *
     * @param  array{headline: string, url: string, datePublished: DateTimeInterface|string, dateModified?: DateTimeInterface|string|null, description?: ?string, image?: string|list<string>|null, author?: ?string, type?: string}  $data
     */
    public static function article(array $data): array
    {
        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => $data['type'] ?? 'Article',
            'headline' => $data['headline'],
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? null,
            'url' => $data['url'],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $data['url']],
            'datePublished' => self::date($data['datePublished']),
            'dateModified' => self::date($data['dateModified'] ?? $data['datePublished']),
            'inLanguage' => app()->getLocale(),
            'author' => isset($data['author']) ? ['@type' => 'Person', 'name' => $data['author']] : null,
            'publisher' => ['@id' => SeoManager::absoluteUrl().'#organization'],
        ]);
    }

    /**
     * Encode for a <script type="application/ld+json"> block. Unicode is kept
     * readable; "<", ">" and "&" are hex-escaped so the payload can never
     * close the script tag or inject markup.
     */
    public static function encode(array $data): string
    {
        return json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR
        );
    }

    /** Recursively drop null, empty-string and empty-array values. */
    public static function clean(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = self::clean($value);
                $data[$key] = $value;
            }

            if ($value === null || $value === '' || $value === []) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected static function date(DateTimeInterface|string $date): string
    {
        return $date instanceof DateTimeInterface ? $date->format(DateTimeInterface::ATOM) : $date;
    }
}
