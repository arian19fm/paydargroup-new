<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

/** Creates the menu locations (without items). */
class MenusSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Menu::LOCATIONS as $location) {
            Menu::firstOrCreate(['location' => $location], ['name' => __('admin.menus.locations.'.$location)]);
        }
    }
}
