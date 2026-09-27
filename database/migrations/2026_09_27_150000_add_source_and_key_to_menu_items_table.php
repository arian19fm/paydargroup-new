<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `key` is a stable identifier the seeder uses to create the default
     * items once (and never duplicate them on later deploys); `source`
     * marks an item whose children are generated automatically from
     * managed content (e.g. the published businesses).
     */
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('key', 64)->nullable()->after('parent_id');
            $table->string('source', 32)->nullable()->after('page_id');

            $table->unique(['menu_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropUnique(['menu_id', 'key']);
            $table->dropColumn(['key', 'source']);
        });
    }
};
