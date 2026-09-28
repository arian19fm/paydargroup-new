<?php

namespace Tests\Feature;

use App\Support\Settings\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class HomeContentSettingsTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    protected bool $keepImportedContent = true;

    public function test_designed_copy_is_in_the_settings_the_admin_edits(): void
    {
        $html = $this->actingAs($this->adminUser('admin'))->get('/admin/settings/home')->assertOk()->getContent();

        foreach (['hero', 'products', 'blog', 'faq', 'contact'] as $section) {
            $this->assertStringContainsString(e(__('settings.home.sections.'.$section)), $html);
        }
        $this->assertStringContainsString('پـایـــدار؛ سـاختـن آیـنـده،', $html);
        $this->assertStringContainsString('سؤال دیگری دارید؟', $html);
        $this->assertStringContainsString('ایـنجاییم تا کمـکتان کنیـم', $html);
    }

    public function test_edits_in_the_admin_change_the_home_page(): void
    {
        $admin = $this->adminUser('admin');
        $values = app(Settings::class)->group('home');
        $values['hero_title_line_1'] = 'عنوان تازهٔ صفحه';
        $values['faq_card_title'] = 'پرسش دیگری هست؟';
        $values['faq_card_cta_url'] = '/contact';
        $values['contact_text'] = '';

        $this->actingAs($admin)->put('/admin/settings/home', ['values' => $values])->assertSessionHasNoErrors();

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('عنوان تازهٔ صفحه', $html);
        $this->assertStringContainsString('پرسش دیگری هست؟', $html);
        $this->assertStringContainsString('href="/contact"', $html);
        $this->assertStringNotContainsString('pg-contact__text', $html);
        $this->assertStringNotContainsString('می‌توانید از طریق فرم زیر', $html);
        $this->assertStringNotContainsString('سؤال دیگری دارید؟', $html);

        // The contact page shares the home contact intro.
        preg_match('/class="pg-contact-page__intro">(.*?)<\/div>/s', $this->get('/contact')->getContent(), $intro);
        $this->assertStringNotContainsString('pg-contact-page__text', $intro[1]);
    }

    public function test_blank_texts_are_left_out_and_the_h1_never_disappears(): void
    {
        foreach (['hero_title_line_1', 'hero_title_line_2', 'hero_cta_label', 'products_card_cta_label', 'faq_ask_placeholder'] as $key) {
            app(Settings::class)->set('home.'.$key, null);
        }

        $html = $this->get('/')->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*class="pg-hero__title">\s*Paydar Group\s*<\/h1>/', $html);
        $this->assertStringNotContainsString('pg-hero__cta', $html);
        $this->assertStringNotContainsString('pg-product__more', $html);
        $this->assertStringNotContainsString('pg-ask', $html);
    }

    public function test_links_reject_unsafe_schemes(): void
    {
        $admin = $this->adminUser('admin');

        foreach (['javascript:alert(1)', 'data:text/html,x', 'ftp://example.com'] as $bad) {
            $this->actingAs($admin)->put('/admin/settings/home', ['values' => ['hero_cta_url' => $bad]])
                ->assertSessionHasErrors('values.hero_cta_url');
        }

        foreach (['https://example.com/a', '/about', '#contact'] as $good) {
            $this->actingAs($admin)->put('/admin/settings/home', ['values' => ['hero_cta_url' => $good]])
                ->assertSessionHasNoErrors();
        }
    }
}
