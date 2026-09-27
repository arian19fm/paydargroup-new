<?php

namespace App\Support\Menus;

use App\Models\Business;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Resolves a menu location into a render-ready tree, cached per location.
 * Items whose target cannot be resolved (an unpublished or missing page)
 * are dropped so the public site never shows dead links, and items with a
 * `source` get their children generated from managed content: the
 * published businesses, in their display order.
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

                if (! $menu) {
                    return [];
                }

                $items = $menu->items()->active()->with('page')->get();
                $publishedSlugs = $this->publishedSlugs($items);
            } catch (QueryException) {
                return [];
            }

            return $this->build($items->whereNull('parent_id'), $items, $publishedSlugs);
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

        // Menus may live at any location string the admin typed.
        try {
            Menu::query()->pluck('location')->each(fn (string $location) => $this->forget($location));
        } catch (QueryException) {
            // No menus table yet (fresh install) — nothing to flush.
        }
    }

    /**
     * Slugs of the published pages that URL items ("/about") point at, so
     * those links can be hidden until the page exists and is live.
     *
     * @return list<string>
     */
    protected function publishedSlugs(Collection $items): array
    {
        $slugs = $items->map(fn (MenuItem $item) => $item->linkedPageSlug())->filter()->unique()->values();

        if ($slugs->isEmpty()) {
            return [];
        }

        return Page::query()->published()->whereIn('slug', $slugs)->pluck('slug')->all();
    }

    protected function build(Collection $nodes, Collection $all, array $publishedSlugs): array
    {
        $out = [];

        foreach ($nodes as $item) {
            /** @var MenuItem $item */
            $url = $item->resolvedUrl();
            $slug = $item->linkedPageSlug();

            if ($url === null || ($slug !== null && ! in_array($slug, $publishedSlugs, true))) {
                continue;
            }

            $out[] = [
                'label' => $item->label,
                'url' => $url,
                'target' => $item->target,
                'children' => [
                    ...$this->sourceChildren($item),
                    ...$this->build($all->where('parent_id', $item->id), $all, $publishedSlugs),
                ],
            ];
        }

        return $out;
    }

    /**
     * Generated children of a source item. Manually added children (if
     * any) follow them.
     */
    protected function sourceChildren(MenuItem $item): array
    {
        if (! $item->hasSource()) {
            return [];
        }

        return match ($item->source) {
            'businesses' => Business::query()->published()->ordered()->get(['id', 'title', 'slug'])
                ->map(fn (Business $business) => [
                    'label' => $business->title,
                    'url' => $business->publicUrl(),
                    'target' => '_self',
                    'children' => [],
                ])->all(),
            default => [],
        };
    }

    protected function cacheKey(string $location): string
    {
        return 'menus:'.$location;
    }
}
