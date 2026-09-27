<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * Creates the menu locations and their default items. Safe to run on every
 * deploy: each default item has a stable `key` and is created only when
 * that key is missing, so edits made in the admin (labels, order, an item
 * switched off) are never overwritten and nothing is ever duplicated. To
 * hide a default item, deactivate it instead of deleting it — a deleted
 * key would come back on the next run.
 *
 * Links to CMS pages ("/about", "/privacy", …) are stored as URLs and stay
 * hidden on the site until a published page with that slug exists; the
 * "businesses" item lists the published businesses automatically.
 */
class MenusSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Menu::LOCATIONS as $location) {
            $menu = Menu::firstOrCreate(['location' => $location], ['name' => __('admin.menus.locations.'.$location)]);

            foreach ($this->defaults($location) as $key => $attributes) {
                if ($menu->items()->where('key', $key)->exists()) {
                    continue;
                }

                $menu->items()->create([
                    'key' => $key,
                    'label' => __('nav.'.$key),
                    'target' => '_self',
                    'is_active' => true,
                    ...$attributes,
                ]);
            }
        }
    }

    /**
     * Default items per location: key => attributes (the label is the
     * `nav.{key}` translation). Order is the sort_order.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function defaults(string $location): array
    {
        $items = match ($location) {
            'main' => [
                'home' => ['url' => '/'],
                'about' => ['url' => '/about'],
                'businesses' => ['source' => 'businesses', 'url' => MenuItem::sourceUrl('businesses')],
                'team' => ['url' => '/team'],
                'careers' => ['url' => '/careers'],
                'articles' => ['url' => '/articles'],
                'contact' => ['url' => '/contact'],
            ],
            'footer' => [
                'about' => ['url' => '/about'],
                'team' => ['url' => '/team'],
                'careers' => ['url' => '/careers'],
                'articles' => ['url' => '/articles'],
                'contact' => ['url' => '/contact'],
            ],
            'legal' => [
                'privacy' => ['url' => '/privacy'],
                'terms' => ['url' => '/terms'],
            ],
            default => [],
        };

        $order = 0;

        foreach ($items as $key => $attributes) {
            $items[$key] = ['sort_order' => $order += 10, ...$attributes];
        }

        return $items;
    }
}
