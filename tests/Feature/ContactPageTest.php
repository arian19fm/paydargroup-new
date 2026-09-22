<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Support\Settings\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_renders_form_with_one_h1_light_header_and_seo(): void
    {
        $html = $this->get('/contact')->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringContainsString('<title>'.__('contact.title').' | Paydar Group</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="'.__('contact.description').'">', $html);
        $this->assertStringContainsString('action="'.route('contact.store').'"', $html);
        $this->assertStringContainsString('name="website"', $html);
        $this->assertStringContainsString('pg-header--light', $html);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
        // No channels or map configured → nothing invented.
        $this->assertStringNotContainsString('pg-channel ', $html);
        $this->assertStringNotContainsString('pg-contact-page__map', $html);
    }

    public function test_channels_and_map_come_from_settings(): void
    {
        $map = Media::factory()->create(['alt_text' => 'نقشه']);
        $settings = app(Settings::class);
        $settings->set('contact.address', 'تهران، خیابان نمونه');
        $settings->set('contact.phone', '021-88063185');
        $settings->set('contact.working_hours', 'روزهای عادی از 9 صبح الی 20 شب');
        $settings->set('contact.email', 'support@paydar.group');
        $settings->set('contact.map_image_media_id', $map->id);
        $settings->set('contact.map_url', 'https://maps.example.com/office');

        $html = $this->get('/contact')->getContent();

        $this->assertStringContainsString('تهران، خیابان نمونه', $html);
        $this->assertStringContainsString('href="tel:02188063185" dir="ltr">۰۲۱-۸۸۰۶۳۱۸۵</a>', $html);
        $this->assertStringContainsString('(روزهای عادی از ۹ صبح الی ۲۰ شب)', $html);
        $this->assertStringContainsString('href="mailto:support@paydar.group" dir="ltr">support@paydar.group</a>', $html);
        $this->assertStringContainsString('<a href="https://maps.example.com/office" target="_blank" rel="noopener"', $html);
        $this->assertStringContainsString('alt="نقشه"', $html);
    }

    public function test_submission_from_the_contact_page_returns_to_it(): void
    {
        $this->from('/contact')->post(route('contact.store'), [
            'phone' => '09123456789',
            'message' => 'پیام آزمایشی با طول کافی است.',
        ])->assertRedirect(url('/contact').'#contact')->assertSessionHas('contact_sent', true);

        $this->get('/contact')->assertSee(__('home.contact.sent'));
    }

    public function test_home_page_form_still_returns_home(): void
    {
        $this->from('/')->post(route('contact.store'), [
            'phone' => '09123456789',
            'message' => 'پیام آزمایشی با طول کافی است.',
        ])->assertRedirect(url('/').'#contact');
    }

    public function test_contact_is_a_reserved_slug_and_in_the_sitemap(): void
    {
        $this->assertContains('contact', config('cms.reserved_slugs'));
        $this->get('/sitemap.xml')->assertOk()->assertSee('https://paydargroup.test/contact', false);
    }
}
