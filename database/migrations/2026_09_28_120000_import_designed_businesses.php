<?php

use App\Support\Menus\MenuRepository;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * The home page, the footer "services" column and the businesses menu
     * used to fall back to a designed line-up (config/home.php) while no
     * business was published, so five cards were visible on the site but
     * the admin list and the menu were empty. Move that line-up into the
     * table once — only when no business exists at all, trashed included —
     * with its images copied into the media library, so the admin manages
     * exactly what is shown.
     */
    public function up(): void
    {
        if (DB::table('businesses')->exists()) {
            return;
        }

        $tagline = 'سکوی تأمین مالی جمعی و عرضه توکن دارایی';
        $features = ['سرمایه گذاری در بازارها', 'گزارش دوره‌ای طرح', 'خرید و فروش آنی', 'عرضه اولیه توکن'];
        $items = [
            ['fund', 'پایدار فاند', 'paydar-fund', 'product-fund'],
            ['exchange', 'پایدار اکسچینج', 'paydar-exchange', 'product-exchange'],
            ['broker', 'پایدار بروکر', 'paydar-broker', 'product-broker'],
            ['ai', 'پایدار AI', 'paydar-ai', 'product-ai'],
            ['bot', 'پایدار بات', 'paydar-bot', 'product-bot'],
        ];

        $now = now();

        foreach ($items as $index => [$accent, $title, $slug, $image]) {
            DB::table('businesses')->insert([
                'title' => $title,
                'slug' => $slug,
                'tagline' => $tagline,
                'features' => json_encode($features, JSON_UNESCAPED_UNICODE),
                'accent' => $accent,
                'image_media_id' => $this->importImage($image, 'نمای محصول '.$title, $title, $now),
                'status' => 'published',
                'published_at' => $now,
                'sort_order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        app(MenuRepository::class)->flush();
        Cache::forget(config('seo.sitemap_cache_key', 'seo.sitemap.xml'));
    }

    /** Businesses are content now; rolling back leaves them in place. */
    public function down(): void {}

    /** Copy a designed image into the media library and return its media ID. */
    protected function importImage(string $name, string $alt, string $title, $now): ?int
    {
        $source = public_path("images/home/{$name}.webp");

        if (! is_file($source)) {
            return null;
        }

        $path = "media/designed/{$name}.webp";
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            $disk->put($path, file_get_contents($source));
        }

        $existing = DB::table('media')->where('disk', 'public')->where('path', $path)->value('id');

        if ($existing) {
            return $existing;
        }

        [$width, $height] = getimagesize($source) ?: [null, null];

        return DB::table('media')->insertGetId([
            'disk' => 'public',
            'path' => $path,
            'filename' => "{$name}.webp",
            'original_filename' => "{$name}.webp",
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => filesize($source),
            'width' => $width,
            'height' => $height,
            'alt_text' => $alt,
            'title' => $title,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
