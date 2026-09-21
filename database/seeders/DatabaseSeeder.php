<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Structural seed data only: roles/permissions, menu locations and blank
 * settings keys. No users (use `php artisan admin:create`) and no content.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            MenusSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
