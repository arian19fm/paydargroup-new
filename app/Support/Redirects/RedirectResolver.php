<?php

namespace App\Support\Redirects;

use App\Models\Redirect;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Looks up an active redirect for a request path with a per-path cache
 * (negative results are cached too), so the redirects table is never
 * scanned on ordinary requests.
 */
class RedirectResolver
{
    /** Paths that may never be redirected (application infrastructure). */
    public const PROTECTED_PREFIXES = [
        '/admin', '/build', '/storage', '/up', '/robots.txt', '/sitemap.xml', '/vendor',
    ];

    public const CACHE_TTL_SECONDS = 3600;

    public const MAX_CHAIN = 5;

    /** @return array{id: int, destination_url: string, http_status: int}|null */
    public function resolve(string $path): ?array
    {
        $path = self::normalizePath($path);

        if (self::isProtected($path)) {
            return null;
        }

        $cached = Cache::remember($this->cacheKey($path), self::CACHE_TTL_SECONDS, function () use ($path) {
            try {
                $redirect = Redirect::query()->active()->where('source_path', $path)->first(['id', 'destination_url', 'http_status']);
            } catch (QueryException) {
                return false;
            }

            return $redirect ? $redirect->only(['id', 'destination_url', 'http_status']) : false;
        });

        return $cached === false ? null : $cached;
    }

    public function forget(string $path): void
    {
        Cache::forget($this->cacheKey(self::normalizePath($path)));
    }

    /**
     * Record a hit without adding latency to the redirect response.
     */
    public function recordHit(int $id): void
    {
        defer(fn () => Redirect::whereKey($id)->update([
            'hit_count' => DB::raw('hit_count + 1'),
            'last_hit_at' => now(),
        ]));
    }

    /**
     * Would a rule source → destination create a loop, following existing
     * active internal redirects up to MAX_CHAIN hops?
     */
    public function wouldLoop(string $source, string $destination, ?int $ignoreId = null): bool
    {
        $source = self::normalizePath($source);
        $next = self::internalPath($destination);

        for ($hop = 0; $next !== null && $hop < self::MAX_CHAIN; $hop++) {
            if ($next === $source) {
                return true;
            }

            $existing = Redirect::query()->active()->where('source_path', $next)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->first(['destination_url']);

            $next = $existing ? self::internalPath($existing->destination_url) : null;
        }

        return false;
    }

    public static function isProtected(string $path): bool
    {
        foreach (self::PROTECTED_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return true;
            }
        }

        return false;
    }

    /** Leading slash, no trailing slash (except root), no query string. */
    public static function normalizePath(string $path): string
    {
        $path = strtok($path, '?') ?: '/';
        $path = '/'.trim($path, '/');

        return $path;
    }

    /**
     * The path component of a destination when it points at this site
     * (relative path or absolute URL under APP_URL); null for external URLs.
     */
    public static function internalPath(string $destination): ?string
    {
        if (str_starts_with($destination, '/')) {
            return self::normalizePath($destination);
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $host = parse_url($destination, PHP_URL_HOST);

        if ($host && $appHost && strcasecmp($host, $appHost) === 0) {
            return self::normalizePath(parse_url($destination, PHP_URL_PATH) ?: '/');
        }

        return null;
    }

    protected function cacheKey(string $path): string
    {
        return 'redirects:'.sha1($path);
    }
}
