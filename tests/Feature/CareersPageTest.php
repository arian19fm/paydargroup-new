<?php

namespace Tests\Feature;

use App\Models\JobOpening;
use App\Models\Media;
use App\Support\Settings\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareersPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_careers_page_renders_designed_copy_benefits_and_empty_state(): void
    {
        $html = $this->get('/careers')->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringContainsString('<title>'.__('careers.title').' | Paydar Group</title>', $html);
        $this->assertStringContainsString(__('careers.hero_title'), $html);
        $this->assertStringContainsString('/images/careers/hero.webp', $html);
        $this->assertSame(4, substr_count($html, 'class="pg-benefit-card__title"'));
        $this->assertStringContainsString(__('careers.benefits.1.title'), $html);
        $this->assertStringContainsString('href="'.route('contact').'"', $html);
        $this->assertStringContainsString(__('careers.jobs_empty'), $html);
        $this->assertStringContainsString('pg-header--light', $html);
        $this->assertContains('careers', config('cms.reserved_slugs'));
    }

    public function test_settings_override_the_copy_and_hero_photo(): void
    {
        $photo = Media::factory()->create(['alt_text' => 'دفتر ما']);
        $settings = app(Settings::class);
        $settings->set('careers.hero_title', 'عنوان سفارشی');
        $settings->set('careers.hero_image_media_id', $photo->id);
        $settings->set('careers.benefit_1_title', 'مزیت سفارشی');
        $settings->set('careers.benefits_cta_url', 'https://example.com/join');
        $settings->set('careers.jobs_empty', 'فعلاً خبری نیست');

        $html = $this->get('/careers')->assertOk()->getContent();

        $this->assertStringContainsString('عنوان سفارشی', $html);
        $this->assertStringContainsString('alt="دفتر ما"', $html);
        $this->assertStringNotContainsString('/images/careers/hero.webp', $html);
        $this->assertStringContainsString('مزیت سفارشی', $html);
        $this->assertStringNotContainsString(__('careers.benefits.1.title'), $html);
        $this->assertStringContainsString('href="https://example.com/join"', $html);
        $this->assertStringContainsString('فعلاً خبری نیست', $html);
    }

    public function test_active_openings_render_in_order_with_badges_links_and_pagination(): void
    {
        JobOpening::factory()->create(['title' => 'مدیر فروش استراتژیک', 'category' => 'فروش', 'tone' => 'orange', 'sort_order' => 2, 'apply_url' => 'jobs@example.com', 'location' => 'تهران / حضوری', 'employment_type' => 'تمام‌وقت']);
        JobOpening::factory()->create(['title' => 'طراح محصول دیجیتال', 'category' => 'طراحی محصول', 'tone' => 'blue', 'sort_order' => 1, 'apply_url' => 'https://example.com/apply/1', 'description' => 'به دنبال طراح محصولی هستیم.']);
        JobOpening::factory()->create(['title' => 'موقعیت غیرفعال', 'is_active' => false]);
        JobOpening::factory()->create(['title' => 'بدون لینک', 'apply_url' => null, 'sort_order' => 3, 'category' => null, 'location' => null, 'employment_type' => null, 'description' => null]);
        JobOpening::factory()->count(6)->create(['sort_order' => 9, 'title' => 'کارآموز']);

        $html = $this->get('/careers')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/pg-job--blue.*طراح محصول دیجیتال.*pg-job--orange.*مدیر فروش استراتژیک.*بدون لینک/s', $html);
        $this->assertStringContainsString('href="https://example.com/apply/1" target="_blank" rel="noopener"', $html);
        $this->assertStringContainsString('href="mailto:jobs@example.com" aria-label="'.__('careers.apply_label', ['title' => 'مدیر فروش استراتژیک']).'"', $html);
        $this->assertStringContainsString('به دنبال طراح محصولی هستیم.', $html);
        $this->assertStringContainsString('<span class="pg-job__badge"><span>فروش</span>', $html);
        $this->assertStringNotContainsString('موقعیت غیرفعال', $html);
        $this->assertSame(7, substr_count($html, 'class="pg-job '));
        $this->assertStringContainsString('pg-careers-jobs__page is-active', $html);
        $this->assertStringContainsString('href="'.route('careers', ['page' => 2]).'#jobs"', $html);
        $this->assertSame(1, substr_count($html, 'class="pg-careers-jobs__page" href=')); // nine openings → two pages: one link + the current pill
        $this->assertStringNotContainsString(__('careers.jobs_empty'), $html);

        $this->get('/careers?page=2')->assertOk()->assertSee('کارآموز');
        $this->get('/sitemap.xml')->assertOk()->assertSee('https://paydargroup.test/careers', false);
    }
}
