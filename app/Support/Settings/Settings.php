<?php

namespace App\Support\Settings;

use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

/**
 * Typed, cached access to site settings. Keys are "group.key" and must be
 * declared in config/settings.php. The whole table is cached as one array
 * and invalidated on every write, so reads never hit the database in
 * steady state.
 *
 *     settings('general.site_name', config('site.name'));
 *     app(Settings::class)->set('seo.default_description', '...');
 */
class Settings
{
    /** @var array<string, mixed>|null */
    protected ?array $loaded = null;

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        if (! array_key_exists($key, $all) || $all[$key] === null || $all[$key] === '') {
            return $default;
        }

        return $all[$key];
    }

    /** All values for one group, keyed by short key. */
    public function group(string $group): array
    {
        $out = [];

        foreach ($this->schema($group)['keys'] as $key => $definition) {
            $out[$key] = $this->get($group.'.'.$key);
        }

        return $out;
    }

    /** All settings marked public (for frontend use). */
    public function public(): array
    {
        $out = [];

        foreach (config('settings.groups') as $group => $definition) {
            foreach ($definition['keys'] as $key => $meta) {
                if ($meta['public'] ?? false) {
                    $out[$group.'.'.$key] = $this->get($group.'.'.$key);
                }
            }
        }

        return $out;
    }

    public function set(string $key, mixed $value): void
    {
        [$group, $short] = $this->split($key);
        $definition = $this->schema($group)['keys'][$short] ?? null;

        if ($definition === null) {
            throw new InvalidArgumentException("Unknown setting [{$key}].");
        }

        Setting::updateOrCreate(
            ['group' => $group, 'key' => $short],
            [
                'value' => $this->serialize($value, $definition['type']),
                'type' => $definition['type'],
                'is_public' => (bool) ($definition['public'] ?? false),
            ]
        );

        $this->flush();
    }

    /** @param  array<string, mixed>  $values  short key => value */
    public function setGroup(string $group, array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($group.'.'.$key, $value);
        }
    }

    /** Ensure a row exists for every declared key (used by the seeder). */
    public function ensureDeclaredKeysExist(): void
    {
        foreach (config('settings.groups') as $group => $definition) {
            foreach ($definition['keys'] as $key => $meta) {
                Setting::firstOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['type' => $meta['type'], 'is_public' => (bool) ($meta['public'] ?? false), 'value' => null]
                );
            }
        }

        $this->flush();
    }

    public function flush(): void
    {
        $this->loaded = null;
        Cache::forget(config('settings.cache_key'));
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        if ($this->loaded !== null) {
            return $this->loaded;
        }

        return $this->loaded = Cache::rememberForever(config('settings.cache_key'), function () {
            try {
                return Setting::query()
                    ->get(['group', 'key', 'value', 'type'])
                    ->mapWithKeys(fn (Setting $s) => [$s->group.'.'.$s->key => $this->unserialize($s->value, $s->type)])
                    ->all();
            } catch (QueryException) {
                // Table not migrated yet (fresh install, some test contexts):
                // behave as if nothing is configured.
                return [];
            }
        });
    }

    public function schema(string $group): array
    {
        $schema = config('settings.groups.'.$group);

        if ($schema === null) {
            throw new InvalidArgumentException("Unknown settings group [{$group}].");
        }

        return $schema;
    }

    /** @return array{0: string, 1: string} */
    protected function split(string $key): array
    {
        $parts = explode('.', $key, 2);

        if (count($parts) !== 2) {
            throw new InvalidArgumentException("Setting key [{$key}] must be group.key.");
        }

        return $parts;
    }

    protected function serialize(mixed $value, string $type): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL) ? '1' : '0',
            'integer', 'media' => (string) (int) $value,
            'json' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            default => (string) $value,
        };
    }

    protected function unserialize(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => $value === '1',
            'integer', 'media' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
