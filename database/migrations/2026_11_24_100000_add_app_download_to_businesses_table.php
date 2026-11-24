<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * App download card of a business on the /apps page (shown when
     * app_enabled is on): the app's own name, description, logo and cover
     * colours, the Android install file (stored on the public disk under
     * apps/) or a link to it, the address iPhone users open to add the web
     * app to their home screen, and the QR code image.
     */
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('app_enabled')->default(false)->after('website_url');
            $table->string('app_name')->nullable()->after('app_enabled');
            $table->text('app_description')->nullable()->after('app_name');
            $table->string('app_color', 7)->nullable()->after('app_description');
            $table->string('app_link_color', 7)->nullable()->after('app_color');
            $table->string('app_android_path', 500)->nullable()->after('app_link_color');
            $table->unsignedBigInteger('app_android_size')->nullable()->after('app_android_path');
            $table->string('app_android_url', 500)->nullable()->after('app_android_size');
            $table->string('app_ios_url', 500)->nullable()->after('app_android_url');
            $table->foreignId('app_logo_media_id')->nullable()->after('app_ios_url')->constrained('media')->nullOnDelete();
            $table->foreignId('app_qr_media_id')->nullable()->after('app_logo_media_id')->constrained('media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('app_qr_media_id');
            $table->dropConstrainedForeignId('app_logo_media_id');
            $table->dropColumn(['app_enabled', 'app_name', 'app_description', 'app_color', 'app_link_color', 'app_android_path', 'app_android_size', 'app_android_url', 'app_ios_url']);
        });
    }
};
