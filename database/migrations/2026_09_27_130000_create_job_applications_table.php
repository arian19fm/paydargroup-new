<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Résumés sent through the careers page. The file itself lives on the
     * private disk (never web-served); `seen_at` drives the admin's unseen
     * counter. Deleting an opening keeps its applications (job_opening_id
     * is nulled).
     */
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_opening_id')->nullable()->constrained('job_openings')->nullOnDelete();
            $table->string('job_title');
            $table->string('phone', 32);
            $table->string('resume_path', 500);
            $table->string('resume_name');
            $table->string('resume_mime', 100);
            $table->unsignedInteger('resume_size');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();

            $table->index(['seen_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
