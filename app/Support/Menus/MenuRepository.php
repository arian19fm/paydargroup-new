<?php

namespace App\Support\Menus;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

/**
 * Resolves a menu location into a render-ready tree, cached per location.
 * Items whose target cannot be resolved (unpublished page) are dropped so
 * the public site never shows dead links.
 */
class MenuRepository
{
    public const CACHE_TTL_SECONDS = 3600;

    /**
     * @return list<array{label: string, url: string, target: string, children: list<array>}>
     */
    public function tree(string $location): array
    {
        return Cache::remember($this->cacheKey($location), self::CACHE_TTL_SECONDS, function () use ($location) {
            try {
                $menu = Menu::query()->where('location', $location)->first();
            } catch (QueryException) {
                return [];
            }

            if (! $menu) {
                return [];
            }

            $items = $menu->items()->active()->with('page')->get();

            return $this->build($items->whereNull('parent_id'), $items);
        });
    }

    public function forget(string $location): void
    {
        Cache::forget($this->cacheKey($location));
    }

    public function flush(): void
    {
        foreach (Menu::LOCATIONS as $location) {
            $this->forget($location);
        }
    }

    protected function build($nodes, $all): array
    {
        $out = [];

        foreach ($nodes as $item) {
            /** @var MenuItem $item */
            $url = $item->resolvedUrl();

            if ($url === null) {
                continue;
            }

            $out[] = [
                'label' => $item->label,
                'url' => $url,
                'target' => $item->target,
                'children' => $this->build($all->where('parent_id', $item->id), $all),
            ];
        }

        return $out;
    }

    protected function cacheKey(string $location): string
    {
        return 'menus:'.$location;
    }
}
