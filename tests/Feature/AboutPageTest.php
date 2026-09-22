<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Page;
use App\Support\Settings\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    protected function aboutPage(): Page
    {
        return Page::factory()->published()->create([
            'slug' => 'about',
            'title' => 'دربـاره پـایـدار گـروپ',
            'excerpt' => 'لید صفحه',
            'content' => "پاراگراف اول\n\nپاراگراف دوم",
            'template' => 'about',
        ]);
    }

    public function test_about_template_renders_page_copy_with_one_h1_and_light_header(): void
    {
        $this->aboutPage();

        $html = $this->get('/about')->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringContainsString('class="pg-about__title"', $html);
        $this->assertStringContainsString('دربـاره پـایـدار گـروپ', $html);
        $this->assertStringContainsString('<p>پاراگراف اول</p>', $html);
        $this->assertStringContainsString(__('about.eyebrow'), $html);
        $this->assertStringContainsString('pg-header--light', $html);
        $this->assertStringNotContainsString('pg-header--overlay', $html);
        // Extras are optional: nothing configured → no partner strip, no history.
        $this->assertStringNotContainsString('pg-about__partners', $html);
        $this->assertStringNotContainsString('pg-about__history"', $html);
        $this->assertStringContainsString('<title>دربـاره پـایـدار گـروپ | Paydar Group</title>', $html);
    }

    public function test_about_extras_come_from_settings_and_ignore_bad_ids(): void
    {
        $this->aboutPage();
        $photo = Media::factory()->create(['alt_text' => 'عکس اول']);
        $logo = Media::factory()->create(['alt_text' => 'لوگو']);
        $settings = app(Settings::class);
        $settings->set('about.image_1_media_id', $photo->id);
        $settings->set('about.image_2_media_id', 999999);
        $settings->set('about.partner_media_ids', $logo->id.', 424242 ,');
        $settings->set('about.history_title', 'تاریخچه');
        $settings->set('about.history_text', 'متن تاریخچه');
        $settings->set('about.stat_clients', 257);
        $settings->set('about.stat_years', 3);

        $html = $this->get('/about')->getContent();

        $this->assertStringContainsString('alt="عکس اول"', $html);
        $this->assertSame(1, substr_count($html, '<li><img'), 'only the resolvable logo renders');
        $this->assertStringContainsString('<h2 id="about-history-heading"', $html);
        $this->assertStringContainsString('<dd>۲۵۷</dd>', $html);
        $this->assertStringContainsString('<dd>۳</dd>', $html);
        $this->assertStringNotContainsString(__('about.stats.companies'), $html);
    }

    public function test_pages_without_the_template_still_use_the_default_view(): void
    {
        Page::factory()->published()->create(['slug' => 'plain', 'title' => 'صفحهٔ ساده', 'template' => null]);

        $this->get('/plain')->assertOk()->assertSee('صفحهٔ ساده')->assertDontSee('pg-about');
    }

    public function test_draft_about_page_is_not_public(): void
    {
        Page::factory()->create(['slug' => 'about', 'template' => 'about']);

        $this->get('/about')->assertNotFound();
    }
}
