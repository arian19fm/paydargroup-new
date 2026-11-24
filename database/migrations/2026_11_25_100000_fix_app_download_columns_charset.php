<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The app download columns were added with ALTER TABLE … ADD COLUMN,
     * which carries no character set of its own: MySQL/MariaDB give such a
     * column the *table's* default. On a server where `businesses` has a
     * non-utf8mb4 default (the table's older columns each carry utf8mb4
     * explicitly, so nothing showed until now) the new columns came out in
     * that legacy charset and saving Persian text failed with error 1366.
     *
     * This converts those columns to utf8mb4 in the collation the rest of
     * the table uses, and sets the table default so later columns are right
     * too. Types, lengths, nullability and defaults are unchanged; MODIFY
     * converts stored values, nothing is dropped. A no-op where the columns
     * are already utf8mb4, and skipped on other drivers (SQLite in tests).
     */
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $collation = $this->collation();

        DB::statement("ALTER TABLE `businesses` DEFAULT CHARACTER SET utf8mb4 COLLATE {$collation}");

        Schema::table('businesses', function (Blueprint $table) use ($collation) {
            $table->string('app_name')->nullable()->charset('utf8mb4')->collation($collation)->change();
            $table->text('app_description')->nullable()->charset('utf8mb4')->collation($collation)->change();
            $table->string('app_color', 7)->nullable()->charset('utf8mb4')->collation($collation)->change();
            $table->string('app_link_color', 7)->nullable()->charset('utf8mb4')->collation($collation)->change();
            $table->string('app_android_path', 500)->nullable()->charset('utf8mb4')->collation($collation)->change();
            $table->string('app_android_url', 500)->nullable()->charset('utf8mb4')->collation($collation)->change();
            $table->string('app_ios_url', 500)->nullable()->charset('utf8mb4')->collation($collation)->change();
        });
    }

    /** Nothing to undo: going back would reintroduce the broken charset. */
    public function down(): void {}

    /**
     * The utf8mb4 collation already used by the table's own text columns
     * (so comparisons and joins stay consistent), else the connection's,
     * else utf8mb4_unicode_ci — which both MySQL and MariaDB have.
     */
    protected function collation(): string
    {
        $existing = DB::table('information_schema.columns')
            ->whereRaw('table_schema = database()')
            ->where('table_name', 'businesses')
            ->where('column_name', 'title')
            ->value('collation_name');

        foreach ([$existing, DB::connection()->getConfig('collation'), 'utf8mb4_unicode_ci'] as $candidate) {
            if (is_string($candidate) && preg_match('/^utf8mb4_[a-z0-9_]+$/', $candidate)) {
                return $candidate;
            }
        }

        return 'utf8mb4_unicode_ci';
    }
};
