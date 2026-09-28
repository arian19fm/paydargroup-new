<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * The home page texts used to come from lang/fa/home.php whenever the
     * matching setting was blank, so the admin showed empty fields while
     * the site showed text. Write the designed copy (and the two designed
     * background photos, copied into the media library) into the
     * Settings → home rows that are still blank; values an admin already
     * entered are kept.
     */
    public function up(): void
    {
        $now = now();

        $values = [
            'hero_eyebrow' => ['string', 'درخـواست مشـاوره در هـر کجا و هـر زمـان'],
            'hero_title_line_1' => ['string', 'پـایـــدار؛ سـاختـن آیـنـده،'],
            'hero_title_line_2' => ['string', 'روی زیـرسـاخـتـی نـویـن'],
            'hero_text' => ['text', 'هر محصول از پایه با تمرکز بر توکن‌سازی دارایی و شفافیت ساخته شده است؛ نه به‌عنوان یک قابلیت جانبی، بلکه به‌عنوان هسته‌ی کار. ؛ نه به‌عنوان یک قابلیت جانبی، بلکه به‌عنوان هسته‌ی کار. ؛ نه به‌عنوان یک قابلیت جانبی، بلکه به‌عنوان هسته‌ی کار.'],
            'hero_cta_label' => ['string', 'دربـاره مـا'],
            'products_eyebrow' => ['string', 'درخـواست مشـاوره در هـر کجا و هـر زمـان'],
            'products_title_highlight' => ['string', 'مــحـــصـــولات مــــا؛'],
            'products_title' => ['string', 'سـاختــه‌شـده روی زیــرساخــت پایـدار'],
            'products_text' => ['text', 'هر محصول از پایه با تمرکز بر توکن‌سازی دارایی و شفافیت ساخته شده است؛ نه به‌عنوان یک قابلیت جانبی، بلکه به‌عنوان هسته‌ی کار.'],
            'products_cta_label' => ['string', 'مشـاهده بیـشـتر'],
            'products_card_cta_label' => ['string', 'مشـاهده بیـشتر'],
            'blog_eyebrow' => ['string', 'اخبار جدید'],
            'blog_title' => ['string', 'تـازه تریـن مطالـب'],
            'blog_title_highlight' => ['string', 'بــلاگ'],
            'blog_text' => ['text', 'با تحلیل‌های کارشناسی، روندهای بازار و نکته‌های کاربردی درباره سرمایه‌گذاری و توکن‌سازی دارایی، همیشه یک قدم جلوتر باشید.'],
            'blog_cta_label' => ['string', 'مشاهده بیشتر'],
            'blog_empty_text' => ['string', 'هنوز مطلبی منتشر نشده است.'],
            'faq_eyebrow' => ['string', 'پاسخ سؤالات شما، در هر کجا و هر زمان'],
            'faq_title_highlight' => ['string', 'سؤالات متداول؛'],
            'faq_title' => ['string', 'پاسخ‌های روشن و شفاف'],
            'faq_ask_placeholder' => ['string', 'سؤالتان را از دستیار هوشمند پایدار بپرسید…'],
            'faq_card_title' => ['string', 'سؤال دیگری دارید؟'],
            'faq_card_text' => ['text', 'کارشناسان پایدار آماده‌اند تا درباره‌ی طرح‌ها، فرایند سرمایه‌گذاری و توکن‌ها، پاسخ دقیق و شخصی به شما بدهند.'],
            'faq_card_cta_label' => ['string', 'تـماس با پـشـتیبـانی'],
            'contact_eyebrow' => ['string', 'تــمــاس  بــا  مــا'],
            'contact_title' => ['string', 'ایـنجاییم تا کمـکتان کنیـم'],
            'contact_text' => ['text', 'می‌توانید از طریق فرم زیر، مستقیماً با کارشناسان پایدار در ارتباط باشید؛ در کوتاه‌ترین زمان پاسخ‌گوی شما خواهیم بود.'],
            'contact_submit_label' => ['string', 'ارسـال درخواسـت'],
        ];

        foreach ($values as $key => [$type, $value]) {
            $this->fill($key, $type, $value, $now);
        }

        foreach (['hero_image_media_id' => 'hero', 'contact_image_media_id' => 'contact'] as $key => $image) {
            if ($this->isBlank($key) && ($id = $this->importImage($image, $now))) {
                $this->fill($key, 'media', (string) $id, $now);
            }
        }

        Cache::forget(config('settings.cache_key', 'settings.all'));
    }

    /** Settings are content now; rolling back leaves them in place. */
    public function down(): void {}

    protected function isBlank(string $key): bool
    {
        $value = DB::table('settings')->where('group', 'home')->where('key', $key)->value('value');

        return trim((string) $value) === '';
    }

    protected function fill(string $key, string $type, string $value, $now): void
    {
        if (! $this->isBlank($key)) {
            return;
        }

        $row = ['value' => $value, 'type' => $type, 'is_public' => true, 'updated_at' => $now];
        $query = DB::table('settings')->where('group', 'home')->where('key', $key);

        if ($query->exists()) {
            $query->update($row);
        } else {
            DB::table('settings')->insert([...$row, 'group' => 'home', 'key' => $key, 'created_at' => $now]);
        }
    }

    /** Copy a designed photo into the media library and return its media ID. */
    protected function importImage(string $name, $now): ?int
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
            'alt_text' => null,
            'title' => $name === 'hero' ? 'تصویر بخش اول صفحهٔ اصلی' : 'تصویر بخش تماس صفحهٔ اصلی',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
