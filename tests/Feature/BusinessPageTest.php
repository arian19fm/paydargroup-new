<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Media;
use App\Support\Settings\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_business_page_renders_with_seo_and_one_h1(): void
    {
        $image = Media::factory()->create(['alt_text' => 'نمای پایدار فاند']);
        $video = Media::factory()->create(['mime_type' => 'video/mp4', 'extension' => 'mp4', 'path' => 'media/2026/09/intro.mp4']);
        $poster = Media::factory()->create(['path' => 'media/2026/09/banner.jpg']);
        $business = Business::factory()->published()->create([
            'title' => 'پایدار فاند', 'slug' => 'paydar-fund', 'eyebrow' => 'سامانه ثروت‌سازی پایدار', 'tagline' => 'سکوی تأمین مالی جمعی',
            'features' => ['عرضه اولیه توکن', 'خرید و فروش آنی'], 'accent' => 'exchange',
            'image_media_id' => $image->id, 'excerpt' => 'خلاصهٔ کسب‌وکار', 'content' => "پاراگراف اول\n\nپاراگراف دوم",
            'website_url' => 'https://fund.example.com',
            'benefits_title' => null, 'benefits_text' => 'همه در یک حساب.',
            'benefits' => [['title' => 'گزارش‌دهی شفاف', 'description' => 'در هر لحظه'], ['title' => 'بدون توضیح', 'description' => '']],
            'benefits_media_id' => $video->id, 'benefits_poster_media_id' => $poster->id,
        ]);
        Business::factory()->create(['title' => 'پیش‌نویس مخفی', 'slug' => 'hidden-draft']);

        $html = $this->get('/businesses/paydar-fund')->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringContainsString('<title>پایدار فاند | Paydar Group</title>', $html);
        $this->assertStringContainsString('name="description" content="خلاصهٔ کسب‌وکار"', $html);
        $this->assertStringContainsString('rel="canonical" href="'.$business->publicUrl().'"', $html);
        $this->assertStringContainsString('pg-business--exchange', $html);
        $this->assertStringContainsString('alt="نمای پایدار فاند"', $html);
        $this->assertStringContainsString('عرضه اولیه توکن', $html);
        $this->assertStringContainsString('<p>پاراگراف اول</p>', $html);
        $this->assertStringContainsString('href="https://fund.example.com"', $html);
        $this->assertStringContainsString('سامانه ثروت‌سازی پایدار', $html);
        $this->assertStringContainsString(__('businesses.about', ['name' => 'پایدار فاند']), $html);
        $this->assertStringContainsString(__('businesses.benefits_title', ['name' => 'پایدار فاند']), $html); // default title
        $this->assertStringContainsString('همه در یک حساب.', $html);
        $this->assertSame(2, substr_count($html, 'class="pg-benefit"'));
        $this->assertStringContainsString('<h3 class="pg-benefit__title">گزارش‌دهی شفاف</h3>', $html);
        $this->assertSame(1, substr_count($html, 'class="pg-benefit__desc"'));
        $this->assertStringContainsString('<video', $html);
        $this->assertStringContainsString('poster="'.$poster->url().'"', $html); // the dedicated banner wins over the business image
        $this->assertStringContainsString('src="'.$video->url().'" type="video/mp4"', $html);
        $this->assertStringContainsString('images/icons/business-play.svg', $html);
        $this->assertStringNotContainsString('پیش‌نویس مخفی', $html);
        $this->assertStringContainsString('pg-header--light', $html);
        $this->assertContains('businesses', config('cms.reserved_slugs'));
    }

    public function test_empty_sections_are_skipped(): void
    {
        Business::factory()->published()->create([
            'title' => 'کمینه', 'slug' => 'minimal', 'eyebrow' => null, 'tagline' => null, 'features' => [], 'image_media_id' => null,
            'excerpt' => null, 'content' => null, 'website_url' => null, 'benefits_title' => null, 'benefits_text' => null, 'benefits' => [], 'benefits_media_id' => null,
        ]);

        $html = $this->get('/businesses/minimal')->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringNotContainsString('pg-business-intro', $html);
        $this->assertStringNotContainsString('pg-business-benefits', $html);
        $this->assertStringNotContainsString('pg-business-hero__tags', $html);
    }

    public function test_drafts_and_scheduled_businesses_are_not_public(): void
    {
        Business::factory()->create(['slug' => 'draft-one']);
        Business::factory()->scheduled()->create(['slug' => 'later-one']);

        $this->get('/businesses/draft-one')->assertNotFound();
        $this->get('/businesses/later-one')->assertNotFound();
        $this->get('/businesses/missing')->assertNotFound();
    }

    public function test_home_page_lists_published_businesses_in_order_with_settings_copy(): void
    {
        $image = Media::factory()->create();
        Business::factory()->published()->create(['title' => 'کسب‌وکار دوم', 'slug' => 'second', 'sort_order' => 2, 'accent' => 'bot']);
        Business::factory()->published()->create(['title' => 'کسب‌وکار اول', 'slug' => 'first', 'sort_order' => 1, 'accent' => 'ai', 'image_media_id' => $image->id, 'features' => ['ویژگی یک']]);
        Business::factory()->create(['title' => 'کسب‌وکار پیش‌نویس', 'slug' => 'draft']);

        app(Settings::class)->set('home.products_title_highlight', 'کسب‌وکارهای ما؛');
        app(Settings::class)->set('home.products_text', 'توضیح سفارشی بخش');
        app(Settings::class)->set('home.products_cta_url', 'https://example.com/all');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/pg-product--ai.*کسب‌وکار اول.*pg-product--bot.*کسب‌وکار دوم/s', $html);
        $this->assertSame(2, substr_count($html, 'class="pg-product '));
        $this->assertStringContainsString('href="'.route('businesses.show', 'first').'"', $html);
        $this->assertStringContainsString($image->url(), $html);
        $this->assertStringContainsString('ویژگی یک', $html);
        $this->assertStringContainsString('pg-product__placeholder', $html);
        $this->assertStringContainsString('کسب‌وکارهای ما؛', $html);
        $this->assertStringContainsString('توضیح سفارشی بخش', $html);
        $this->assertStringContainsString('href="https://example.com/all"', $html);
        $this->assertStringContainsString('درخـواست مشـاوره در هـر کجا و هـر زمـان', $html); // filled by the home settings migration
        $this->assertStringNotContainsString('کسب‌وکار پیش‌نویس', $html);
        $this->assertStringNotContainsString('/images/home/product-fund.webp', $html);
    }

    public function test_published_businesses_are_in_the_sitemap(): void
    {
        $published = Business::factory()->published()->create(['slug' => 'in-sitemap']);
        Business::factory()->create(['slug' => 'not-in-sitemap']);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString($published->publicUrl(), $xml);
        $this->assertStringNotContainsString('not-in-sitemap', $xml);
    }
}
