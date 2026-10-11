<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Automatic news (docs/CMS.md → "Automatic news"): one row per run of
     * the writer — scheduled or requested from the admin — with its outcome
     * and token usage. Articles it wrote point back to it and keep the
     * sources they were based on.
     */
    public function up(): void
    {
        Schema::create('news_runs', function (Blueprint $table) {
            $table->id();
            $table->string('trigger', 20); // scheduled | manual
            $table->string('status', 20)->default('queued'); // queued | running | succeeded | failed
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedSmallInteger('articles_count')->default(0);
            $table->string('model', 100)->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedSmallInteger('web_searches')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('news_run_id')->nullable()->after('author_id')->constrained('news_runs')->nullOnDelete();
            $table->json('sources')->nullable()->after('news_run_id');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('news_run_id');
            $table->dropColumn('sources');
        });

        Schema::dropIfExists('news_runs');
    }
};
