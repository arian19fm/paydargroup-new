<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_page_renders_with_one_h1_and_seo(): void
    {
        $html = $this->get('/apps')->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringContainsString('<title>'.__('apps.title').' | Paydar Group</title>', $html);
        $this->assertStringContainsString(__('apps.empty'), $html);
        $this->assertContains('apps', config('cms.reserved_slugs'));
    }

    public function test_only_published_businesses_with_an_enabled_app_are_listed_in_order(): void
    {
        $qr = Media::factory()->create(['path' => 'media/2026/11/qr-fund.png']);
        $logo = Media::factory()->create(['path' => 'media/2026/11/logo-fund.png']);

        Business::factory()->published()->create([
            'title' => 'پایدار اکسچینج', 'slug' => 'paydar-exchange', 'sort_order' => 2, 'excerpt' => 'خلاصهٔ اکسچینج', 'website_url' => null,
            'app_enabled' => true, 'app_android_url' => 'https://store.example.com/exchange',
        ]);
        Business::factory()->published()->create([
            'title' => 'پایدار AI', 'slug' => 'paydar-ai', 'sort_order' => 1, 'tagline' => 'مشاور مالی هوشمند', 'website_url' => 'https://www.pailo.ir/start',
            'app_enabled' => true, 'app_name' => 'پایلو', 'app_description' => 'مشاورهٔ سرمایه‌گذاری آنلاین', 'app_color' => '#4a4294', 'app_link_color' => '#e47182',
            'app_android_path' => 'apps/paydar-ai-abc.apk', 'app_android_size' => 25690112,
            'app_ios_url' => 'https://app.pailo.example.com', 'app_qr_media_id' => $qr->id, 'app_logo_media_id' => $logo->id,
        ]);
        Business::factory()->published()->create(['title' => 'اپ خاموش', 'app_enabled' => false, 'app_ios_url' => 'https://off.example.com']);
        Business::factory()->published()->create(['title' => 'اپ بدون فایل', 'app_enabled' => true]);
        Business::factory()->create(['title' => 'پیش‌نویس اپ', 'app_enabled' => true, 'app_ios_url' => 'https://draft.example.com']);
        Business::factory()->scheduled()->create(['title' => 'اپ زمان‌بندی‌شده', 'app_enabled' => true, 'app_ios_url' => 'https://later.example.com']);

        $page = $this->get('/apps')->assertOk()->getContent();
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $page));

        // The header and footer list every published business; only the page body is under test.
        $html = substr($page, strpos($page, 'class="pg-apps"'), strpos($page, '</main>') - strpos($page, 'class="pg-apps"'));

        // Quick-jump chips carry the business title, cards the app name, both in sort order.
        $this->assertMatchesRegularExpression('/href="#app-paydar-ai".*href="#app-paydar-exchange".*id="app-paydar-ai".*id="app-paydar-exchange"/s', $html);
        $this->assertMatchesRegularExpression('/<h3 class="pg-app-card__name">پایلو<\/h3>\s*<p class="pg-app-card__tagline">مشاور مالی هوشمند<\/p>/', $html);
        $this->assertStringContainsString('style="--pg-app-color: #4a4294; --pg-app-link: #e47182;"', $html);
        $this->assertStringContainsString('مشاورهٔ سرمایه‌گذاری آنلاین', $html);
        $this->assertStringContainsString('href="/storage/apps/paydar-ai-abc.apk"', $html);
        $this->assertStringContainsString('download="paydar-ai.apk"', $html);
        $this->assertStringContainsString(__('apps.android_file'), $html);
        $this->assertStringContainsString('/storage/media/2026/11/qr-fund.png', $html);
        $this->assertStringContainsString("--pg-app-mark: url('/storage/media/2026/11/logo-fund.png')", $html);
        $this->assertStringContainsString('href="https://www.pailo.ir/start"', $html);
        $this->assertStringContainsString('<span dir="ltr">pailo.ir</span>', $html);

        // The iPhone button opens the guide and falls back to the address without JS.
        $this->assertMatchesRegularExpression('/href="https:\/\/app\.pailo\.example\.com"[^>]*data-bs-target="#ios-guide-paydar-ai"/s', $html);
        $this->assertStringContainsString('id="ios-guide-paydar-ai"', $html);
        $this->assertStringContainsString(__('apps.ios_guide.steps')[2], $html);

        // Fallbacks: business title and excerpt, an outside Android link, no iPhone button, website row, logo or QR.
        $exchange = substr($html, strpos($html, 'id="app-paydar-exchange"'), strpos($html, '</article>', strpos($html, 'id="app-paydar-exchange"')) - strpos($html, 'id="app-paydar-exchange"'));
        $this->assertStringContainsString('<h3 class="pg-app-card__name">پایدار اکسچینج</h3>', $exchange);
        $this->assertStringContainsString('خلاصهٔ اکسچینج', $exchange);
        $this->assertStringContainsString('href="https://store.example.com/exchange"', $exchange);
        $this->assertStringContainsString(__('apps.android_link'), $exchange);
        foreach (['style=', 'pg-platform--ios', 'pg-app-card__site', 'pg-app-card__qr', 'pg-app-card__logo'] as $absent) {
            $this->assertStringNotContainsString($absent, $exchange);
        }

        foreach (['اپ خاموش', 'اپ بدون فایل', 'پیش‌نویس اپ', 'اپ زمان‌بندی‌شده'] as $hidden) {
            $this->assertStringNotContainsString($hidden, $html);
        }
        $this->assertStringNotContainsString(__('apps.empty'), $html);
        $this->assertSame(2, substr_count($html, '<h3 class="pg-app-card__name">'));
    }

    public function test_page_is_in_the_sitemap(): void
    {
        $this->get('/sitemaps/pages.xml')->assertOk()->assertSee('/apps</loc>', false);
    }
}
