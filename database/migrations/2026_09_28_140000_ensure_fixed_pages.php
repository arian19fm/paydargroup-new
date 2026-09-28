<?php

use App\Support\Menus\MenuRepository;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Pages are no longer created or deleted in the admin, so the site's
     * pages must exist on their own:
     *
     * - "about" (template `about`, Figma 249:1038 / 258:33) is created
     *   published with the designed copy, and its extras (photos, partner
     *   logos, history, statistics) are written into the blank
     *   Settings → about rows, the images copied into the media library;
     * - "privacy" and "terms" (footer legal links) are created as drafts —
     *   no legal text exists in the design; publish them from the admin
     *   once written.
     *
     * An existing page with one of these slugs is kept as it is (restored
     * if it was trashed); no other page is touched. The now-unused
     * pages.create / pages.delete permissions are removed.
     */
    public function up(): void
    {
        $now = now();

        $this->ensurePage('about', [
            'title' => 'دربـاره پـایـدار گـروپ',
            'excerpt' => 'کانون ایران نوین، اولین و بزرگ‌ترین سازمان تبلیغاتی در کشور، با سال‌ها سابقه درخشان در حوزه تبلیغات، برندینگ و ارائه راهکارهای مؤثر بازاریابی بر پایه به‌روزترین رویکردهای علمی و حرفه‌ای است.',
            'content' => implode("\n\n", [
                'دات‌وان تهاتر به‌عنوان یکی از بازیگران نوآور در زیست‌بوم بازرگانی گروه دات‌وان، به‌دنبال بازآفرینی مفهوم «تسویه غیرنقدی» در اقتصاد ملی است.',
                'دات‌وان تهاتر با طراحی مدل‌های تهاتر چند‌سویه و زنجیره‌ای، بستر مناسبی را برای تسویه دیون و بدهی‌ها، بهره‌برداری از ظرفیت‌های بلااستفاده، و ارتقای نقدینگی سازمان‌ها بدون نیاز به جریان مستقیم نقدی فراهم ساخته است.',
                'دات‌وان تهاتر با استفاده از فناوری‌های دیجیتال و سازوکارهای شفاف، نه‌تنها جایگزینی کارآمد برای تسویه‌های سنتی ایجاد کرده، بلکه در تلاش است با شبکه‌سازی هدفمند، حلقه ارتباطی بین شرکت‌های دولتی، خصوصی، تأمین‌کنندگان، تولیدکنندگان و نهادهای خدماتی را تقویت کند.',
                'مزیت رقابتی دات‌وان تهاتر، در رویکرد تحلیل‌محور، سرعت در تسویه، و ارائه سبد متنوعی از خدمات تبادل غیرنقدی است؛ خدماتی که در شرایط محدودیت نقدینگی یا تحریم، می‌تواند به عنوان راه‌حلی مؤثر در تبادلات اقتصادی ایفای نقش کند.',
                str_repeat('دات‌وان تهاتر با استفاده از فناوری‌های دیجیتال و سازوکارهای شفاف، نه‌تنها جایگزینی کارآمد برای تسویه‌های سنتی ایجاد کرده، بلکه در تلاش است با شبکه‌سازی هدفمند، حلقه ارتباطی بین شرکت‌های دولتی، خصوصی، تأمین‌کنندگان، تولیدکنندگان و نهادهای خدماتی را تقویت کند. ', 2)
                    .'دات‌وان تهاتر با استفاده از فناوری‌های دیجیتال و سازوکارهای شفاف، نه‌تنها جایگزینی کارآمد برای تسویه‌های سنتی ایجاد کرده، بلکه در تلاش است با شبکه‌سازی هدفمند، حلقه ارتباطی بین شرکت‌های دولتی، خصوصی، تأمین‌کنندگان، تولیدکنندگان و نهادهای خدماتی را تقویت کند.',
            ]),
            'template' => 'about',
            'status' => 'published',
            'published_at' => $now,
        ], $now);

        $this->ensurePage('privacy', ['title' => 'حریم خصوصی', 'status' => 'draft'], $now);
        $this->ensurePage('terms', ['title' => 'شرایط استفاده', 'status' => 'draft'], $now);

        $this->fillAboutSettings($now);

        DB::table('permissions')->whereIn('name', ['pages.create', 'pages.delete'])->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        app(MenuRepository::class)->flush();
        Cache::forget(config('seo.sitemap_cache_key', 'seo.sitemap.xml'));
        Cache::forget(config('settings.cache_key', 'settings.all'));
    }

    /** Pages and settings are content now; rolling back leaves them in place. */
    public function down(): void {}

    protected function ensurePage(string $slug, array $attributes, $now): void
    {
        $existing = DB::table('pages')->where('slug', $slug)->first();

        if ($existing) {
            $changes = [];

            if ($existing->deleted_at !== null) {
                $changes['deleted_at'] = null;
            }

            // The about page must render with its template.
            if (($attributes['template'] ?? null) && $existing->template !== $attributes['template']) {
                $changes['template'] = $attributes['template'];
            }

            if ($changes) {
                DB::table('pages')->where('id', $existing->id)->update([...$changes, 'updated_at' => $now]);
            }

            return;
        }

        DB::table('pages')->insert([
            'slug' => $slug,
            'excerpt' => null,
            'content' => null,
            'template' => null,
            'published_at' => null,
            'is_featured' => false,
            ...$attributes,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    protected function fillAboutSettings($now): void
    {
        $images = [
            'image_1_media_id' => ['intro-1.png', 'درباره پایدار گروپ — تصویر اول'],
            'image_2_media_id' => ['intro-2.png', 'درباره پایدار گروپ — تصویر دوم'],
            'history_image_media_id' => ['history.png', 'تاریخچه پایدار گروپ'],
        ];

        foreach ($images as $key => [$file, $title]) {
            if ($this->isBlank($key) && ($id = $this->importImage($file, $title, $now))) {
                $this->fill($key, 'media', (string) $id, $now);
            }
        }

        if ($this->isBlank('partner_media_ids')) {
            $ids = [];

            // Logos of the design's social-proof strip, in order.
            foreach (['Boltshift', 'Lightbox', 'FeatherDev', 'Spherule', 'GlobalBank', 'Nietzsche'] as $index => $name) {
                if ($id = $this->importImage('partner-'.($index + 1).'.png', $name, $now)) {
                    $ids[] = $id;
                }
            }

            if ($ids) {
                $this->fill('partner_media_ids', 'string', implode(',', $ids), $now);
            }
        }

        $this->fill('history_title', 'string', 'تاریخچه پایدار گروپ', $now);
        $this->fill('history_text', 'text', str_repeat('دات‌وان تهاتر با استفاده از فناوری‌های دیجیتال و سازوکارهای شفاف، نه‌تنها جایگزینی کارآمد برای تسویه‌های سنتی ایجاد کرده، بلکه در تلاش است با شبکه‌سازی هدفمند، حلقه ارتباطی بین شرکت‌های دولتی، خصوصی، تأمین‌کنندگان، تولیدکنندگان و نهادهای خدماتی را تقویت کند. ', 2).'دات‌وان تهاتر با استفاده از فناوری‌های دیجیتال و سازوکارهای شفاف،', $now);
        $this->fill('stat_clients', 'integer', '257', $now);
        $this->fill('stat_years', 'integer', '3', $now);
        $this->fill('stat_companies', 'integer', '5', $now);
    }

    protected function isBlank(string $key): bool
    {
        $value = DB::table('settings')->where('group', 'about')->where('key', $key)->value('value');

        return trim((string) $value) === '';
    }

    protected function fill(string $key, string $type, string $value, $now): void
    {
        if (! $this->isBlank($key)) {
            return;
        }

        $row = ['value' => $value, 'type' => $type, 'is_public' => true, 'updated_at' => $now];
        $query = DB::table('settings')->where('group', 'about')->where('key', $key);

        if ($query->exists()) {
            $query->update($row);
        } else {
            DB::table('settings')->insert([...$row, 'group' => 'about', 'key' => $key, 'created_at' => $now]);
        }
    }

    /** Copy a designed image from public/images/about into the media library. */
    protected function importImage(string $file, string $title, $now): ?int
    {
        $source = public_path("images/about/{$file}");

        if (! is_file($source)) {
            return null;
        }

        $path = "media/designed/about-{$file}";
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
            'filename' => "about-{$file}",
            'original_filename' => $file,
            'mime_type' => 'image/png',
            'extension' => 'png',
            'size' => filesize($source),
            'width' => $width,
            'height' => $height,
            'alt_text' => str_starts_with($file, 'partner-') ? $title : null,
            'title' => $title,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
