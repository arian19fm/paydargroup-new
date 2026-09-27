<?php

namespace App\Support\Contact;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Turns the Google Maps link an editor pastes into Settings → contact
 * (a place page, a "share" link, a search URL, an embed URL or even the
 * whole <iframe> snippet) into the URL of an embeddable map. Short share
 * links (maps.app.goo.gl, goo.gl/maps) are resolved once over HTTP and
 * cached; anything that is not a Google Maps link yields null, in which
 * case the page falls back to the static map image.
 */
class GoogleMapsEmbed
{
    protected const SHORT_HOSTS = ['maps.app.goo.gl', 'goo.gl'];

    public static function fromUrl(?string $input): ?string
    {
        $url = self::extractUrl((string) $input);

        if ($url === null) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');

        if (in_array($host, self::SHORT_HOSTS, true)) {
            $resolved = self::resolveShortLink($url);

            return $resolved && $resolved !== $url ? self::fromUrl($resolved) : null;
        }

        if (! self::isGoogleMapsHost($host)) {
            return null;
        }

        $path = $parts['path'] ?? '/';
        parse_str($parts['query'] ?? '', $query);

        // Already an embed URL (from "Share → Embed a map").
        if (str_starts_with($path, '/maps/embed')) {
            return 'https://'.$host.$path.(isset($parts['query']) ? '?'.$parts['query'] : '');
        }

        // Place / directions pages carry the viewport as /@lat,lng,zoomz.
        if (preg_match('#/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)(?:,(\d+(?:\.\d+)?)z)?#', $path, $m)) {
            return self::embed($m[1].','.$m[2], isset($m[3]) ? (int) round((float) $m[3]) : null);
        }

        // Explicit coordinates or a query in the URL parameters.
        foreach (['q', 'query', 'll', 'center', 'destination'] as $key) {
            if (! empty($query[$key]) && is_string($query[$key])) {
                return self::embed($query[$key], isset($query['z']) ? (int) $query['z'] : null);
            }
        }

        // /maps/place/<name>/… or /maps/search/<name>/…
        if (preg_match('#/maps/(?:place|search)/([^/]+)#', $path, $m)) {
            return self::embed(rawurldecode(str_replace('+', ' ', $m[1])));
        }

        return null;
    }

    /** The URL inside an <iframe> snippet, or the trimmed input when it is a URL. */
    protected static function extractUrl(string $input): ?string
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        if (stripos($input, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/i', $input, $m)) {
            $input = html_entity_decode($m[1]);
        }

        if (! preg_match('#^https?://#i', $input)) {
            $input = 'https://'.$input;
        }

        return filter_var($input, FILTER_VALIDATE_URL) ? $input : null;
    }

    protected static function isGoogleMapsHost(string $host): bool
    {
        return $host === 'maps.google.com'
            || str_starts_with($host, 'maps.google.')
            || (preg_match('/^(www\.)?google\.[a-z.]+$/', $host) === 1);
    }

    protected static function embed(string $query, ?int $zoom = null): string
    {
        $params = ['q' => $query, 'hl' => app()->getLocale(), 'output' => 'embed'];

        if ($zoom !== null && $zoom > 0) {
            $params['z'] = min($zoom, 21);
        }

        return 'https://www.google.com/maps?'.http_build_query($params);
    }

    /**
     * Follow a share link's redirect to the full URL. Cached for a week;
     * a failure is remembered for an hour so a blocked network never
     * slows the page down on every request.
     */
    protected static function resolveShortLink(string $url): ?string
    {
        $key = 'contact.map_short.'.md5($url);

        return Cache::remember($key, now()->addWeek(), function () use ($url, $key) {
            try {
                $response = Http::withoutRedirecting()->timeout(4)->connectTimeout(3)->get($url);
                $location = $response->header('Location');

                if ($response->redirect() && $location) {
                    return $location;
                }
            } catch (Throwable) {
                // fall through
            }

            Cache::put($key, null, now()->addHour());

            return null;
        }) ?: null;
    }
}
