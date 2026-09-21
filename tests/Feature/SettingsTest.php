<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\Settings\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_values_are_typed_and_cached(): void
    {
        $settings = app(Settings::class);
        $settings->set('general.site_name', 'گروه پایدار');
        $settings->set('seo.default_og_image_id', '7');

        $this->assertSame('گروه پایدار', settings('general.site_name'));
        $this->assertSame(7, settings('seo.default_og_image_id'));
        $this->assertSame('fallback', settings('contact.phone', 'fallback'));

        // Subsequent reads come from cache, not the database.
        app()->forgetInstance(Settings::class);
        DB::enableQueryLog();
        $this->assertSame('گروه پایدار', settings('general.site_name'));
        $this->assertSame('گروه پایدار', settings('general.site_name'));
        $this->assertCount(0, DB::getQueryLog());
    }

    public function test_unknown_keys_are_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        app(Settings::class)->set('general.not_declared', 'x');
    }

    public function test_settings_feed_site_name_and_seo_defaults(): void
    {
        app(Settings::class)->setGroup('general', ['site_name' => 'نام سایت', 'site_tagline' => 'شعار']);
        app(Settings::class)->set('seo.default_description', 'توضیح پیش‌فرض');

        $this->get('/')
            ->assertSee('<title>نام سایت | شعار</title>', false)
            ->assertSee('<meta name="description" content="توضیح پیش‌فرض">', false)
            ->assertSee('"name":"نام سایت"', false);
    }

    public function test_admin_can_update_a_settings_group(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/admin/settings/contact')->assertOk();

        $this->actingAs($admin)->put('/admin/settings/contact', [
            'values' => ['email' => 'info@example.test', 'phone' => '', 'address' => 'x'],
        ])->assertRedirect('/admin/settings/contact');

        $this->assertSame('info@example.test', settings('contact.email'));
        $this->assertNull(settings('contact.phone'));
        $this->assertDatabaseHas('settings', ['group' => 'contact', 'key' => 'address', 'value' => 'x', 'is_public' => 1]);

        $this->actingAs($admin)->from('/admin/settings/contact')
            ->put('/admin/settings/contact', ['values' => ['email' => 'not-an-email']])
            ->assertSessionHasErrors('values.email');

        $this->actingAs($admin)->get('/admin/settings/nope')->assertNotFound();
        $this->assertGreaterThan(0, Setting::count());
    }
}
