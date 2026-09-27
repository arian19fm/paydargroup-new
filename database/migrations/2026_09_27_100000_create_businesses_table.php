<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Group businesses ("کسب و کارها"): the cards of the home page products
     * section and each one's own page (/businesses/{slug}: hero, intro,
     * benefits with an image or video). Publishing follows the shared
     * draft/published + published_at rule; media references are cleared,
     * never cascaded, when a media item is removed.
     */
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('eyebrow')->nullable();
            $table->string('tagline', 500)->nullable();
            $table->json('features')->nullable();
            $table->string('accent', 32)->default('fund');
            $table->foreignId('image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('website_url', 500)->nullable();
            $table->string('benefits_title')->nullable();
            $table->text('benefits_text')->nullable();
            $table->json('benefits')->nullable();
            $table->foreignId('benefits_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status', 16)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
