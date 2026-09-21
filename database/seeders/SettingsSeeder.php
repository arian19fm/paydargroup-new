<?php

namespace Database\Seeders;

use App\Support\Settings\Settings;
use Illuminate\Database\Seeder;

/** Ensures a blank row exists for every declared setting key. No content. */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        app(Settings::class)->ensureDeclaredKeysExist();
    }
}
