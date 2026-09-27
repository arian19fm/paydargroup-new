<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Job openings listed on the careers page: the card fields, the full
     * description shown in the detail modal (`body`, plain text with `#`
     * headings and `-` bullets) and the spec rows (`specs`, label/value).
     */
    public function up(): void
    {
        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 100)->nullable();
            $table->string('tone', 16)->default('blue');
            $table->string('description', 500)->nullable();
            $table->longText('body')->nullable();
            $table->json('specs')->nullable();
            $table->string('employment_type', 100)->nullable();
            $table->string('location', 100)->nullable();
            $table->string('apply_url', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_openings');
    }
};
