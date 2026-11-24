<?php

namespace Tests\Feature\Admin;

use App\Models\Business;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class BusinessAdminTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_admin_creates_edits_and_deletes_a_business_with_seo(): void
    {
        $admin = $this->adminUser('admin');

        $this->actingAs($admin)->get('/admin/businesses')->assertOk();
        $this->actingAs($admin)->get('/admin/businesses/create')->assertOk();

        $this->actingAs($admin)->post('/admin/businesses', [
            'title' => 'پایدار فاند', 'slug' => 'paydar-fund', 'tagline' => 'سکوی تأمین مالی جمعی',
            'features_text' => "سرمایه گذاری در بازارها\n\n گزارش دوره‌ای طرح \n", 'accent' => 'fund',
            'excerpt' => 'خلاصه', 'content' => 'متن', 'website_url' => 'https://fund.example.com', 'eyebrow' => 'سامانه ثروت‌سازی',
            'benefits_title' => 'مزایا', 'benefits_text' => 'همه در یک حساب', 'benefits_text_lines' => "گزارش‌دهی شفاف | در هر لحظه\nفقط عنوان\n\n | بدون عنوان",
            'status' => 'published', 'sort_order' => 3,
            'seo' => ['title' => 'عنوان سئو', 'robots_index' => 1, 'robots_follow' => 1],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $business = Business::firstOrFail();
        $this->assertSame(['سرمایه گذاری در بازارها', 'گزارش دوره‌ای طرح'], $business->features);
        $this->assertTrue($business->isPublished());
        $this->assertSame('سامانه ثروت‌سازی', $business->eyebrow);
        $this->assertSame([['title' => 'گزارش‌دهی شفاف', 'description' => 'در هر لحظه'], ['title' => 'فقط عنوان', 'description' => '']], $business->benefits);
        $this->assertSame("گزارش‌دهی شفاف | در هر لحظه\nفقط عنوان", $business->benefitsAsText());
        $this->assertSame(3, $business->sort_order);
        $this->assertSame('عنوان سئو', $business->seo->title);
        $this->assertSame($admin->id, $business->created_by);

        $this->actingAs($admin)->get("/admin/businesses/{$business->id}/edit")->assertOk()->assertSee('سرمایه گذاری در بازارها')->assertSee('گزارش‌دهی شفاف | در هر لحظه');

        $this->actingAs($admin)->put("/admin/businesses/{$business->id}", [
            'title' => 'پایدار فاند', 'slug' => 'paydar-fund', 'accent' => 'broker', 'status' => 'draft', 'features_text' => '',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $business->refresh();
        $this->assertSame('broker', $business->accent);
        $this->assertSame([], $business->features);
        $this->assertSame([], $business->benefits);
        $this->assertFalse($business->isPublished());

        $this->actingAs($admin)->get('/admin/businesses?q=فاند')->assertOk()->assertSee('پایدار فاند');

        $this->actingAs($admin)->delete("/admin/businesses/{$business->id}")->assertRedirect('/admin/businesses');
        $this->assertSoftDeleted('businesses', ['id' => $business->id]);
    }

    public function test_image_is_uploaded_from_the_form_and_can_be_removed(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser('admin');

        $this->actingAs($admin)->post('/admin/businesses', [
            'title' => 'پایدار بات', 'accent' => 'bot', 'status' => 'draft',
            'image' => UploadedFile::fake()->image('bot.png', 1024, 768),
            'benefits_media' => UploadedFile::fake()->create('intro.mp4', 500, 'video/mp4'),
            'benefits_poster' => UploadedFile::fake()->image('banner.jpg', 1260, 625),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $business = Business::firstOrFail();
        $this->assertTrue(Media::findOrFail($business->benefits_media_id)->isVideo());
        $this->assertTrue(Media::findOrFail($business->benefits_poster_media_id)->isImage());
        $this->assertSame('paydar-bat', $business->slug); // transliterated when blank
        $image = Media::findOrFail($business->image_media_id);
        $this->assertSame('پایدار بات', $image->title);
        Storage::disk('public')->assertExists($image->path);

        $this->actingAs($admin)->put("/admin/businesses/{$business->id}", [
            'title' => 'پایدار بات', 'slug' => 'paydar-bot', 'accent' => 'bot', 'status' => 'draft',
            'image_media_id' => $image->id, 'remove_image' => 1, 'remove_benefits_media' => 1, 'remove_benefits_poster' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull($business->fresh()->image_media_id);
        $this->assertNull($business->fresh()->benefits_media_id);
        $this->assertNull($business->fresh()->benefits_poster_media_id);
    }

    public function test_app_download_file_qr_and_addresses_are_managed_from_the_form(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser('admin');

        $this->actingAs($admin)->post('/admin/businesses', [
            'title' => 'پایدار فاند', 'slug' => 'paydar-fund', 'accent' => 'fund', 'status' => 'draft',
            'app_enabled' => 1, 'app_name' => 'فاند', 'app_description' => 'توضیح کارت', 'app_color' => '#2A412A', 'app_link_color' => '#a97c50',
            'app_ios_url' => 'https://app.fund.example.com', 'website_url' => 'https://www.paydar.fund',
            'app_logo' => UploadedFile::fake()->image('logo.png', 120, 160),
            'app_android_file' => UploadedFile::fake()->create('fund-release.apk', 2048, 'application/vnd.android.package-archive'),
            'app_qr' => UploadedFile::fake()->image('qr.png', 400, 400),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $business = Business::firstOrFail();
        $first = $business->app_android_path;
        $this->assertTrue($business->app_enabled);
        $this->assertSame('فاند', $business->appTitle());
        $this->assertSame('توضیح کارت', $business->appText());
        $this->assertSame('--pg-app-color: #2a412a; --pg-app-link: #a97c50;', $business->appStyle());
        $this->assertSame('paydar.fund', $business->websiteHost());
        $this->assertTrue(Media::findOrFail($business->app_logo_media_id)->isImage());
        $this->assertMatchesRegularExpression('#^apps/paydar-fund-[a-z0-9]{8}\.apk$#', $first);
        $this->assertSame(2048 * 1024, $business->app_android_size);
        $this->assertSame('/storage/'.$first, $business->androidUrl());
        $this->assertTrue(Media::findOrFail($business->app_qr_media_id)->isImage());
        Storage::disk('public')->assertExists($first);

        $this->actingAs($admin)->get("/admin/businesses/{$business->id}/edit")->assertOk()->assertSee(basename($first))->assertSee('https://app.fund.example.com');

        // A new upload replaces the file and removes the old one.
        $base = ['title' => 'پایدار فاند', 'slug' => 'paydar-fund', 'accent' => 'fund', 'status' => 'draft', 'app_enabled' => 1];
        $this->actingAs($admin)->put("/admin/businesses/{$business->id}", $base + [
            'app_qr_media_id' => $business->app_qr_media_id,
            'app_android_file' => UploadedFile::fake()->create('fund-next.apk', 1024, 'application/zip'),
        ])->assertSessionHasNoErrors();
        $second = $business->fresh()->app_android_path;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        // Saving without touching the file keeps it.
        $this->actingAs($admin)->put("/admin/businesses/{$business->id}", $base)->assertSessionHasNoErrors();
        $this->assertSame($second, $business->fresh()->app_android_path);

        $this->actingAs($admin)->put("/admin/businesses/{$business->id}", ['app_enabled' => 0, 'remove_app_android_file' => 1, 'remove_app_qr' => 1, 'remove_app_logo' => 1] + $base)->assertSessionHasNoErrors();
        $business->refresh();
        $this->assertFalse($business->app_enabled);
        $this->assertNull($business->app_android_path);
        $this->assertNull($business->app_android_size);
        $this->assertNull($business->app_qr_media_id);
        $this->assertNull($business->app_logo_media_id);
        $this->assertSame('پایدار فاند', $business->appTitle());
        $this->assertNull($business->appStyle());
        Storage::disk('public')->assertMissing($second);

        $this->actingAs($admin)->put("/admin/businesses/{$business->id}", $base + [
            'app_android_file' => UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream'),
            'app_ios_url' => 'javascript:alert(1)', 'app_android_url' => 'ftp://x', 'app_color' => 'red; background: url(x)', 'app_link_color' => '#12345',
            'app_logo' => UploadedFile::fake()->image('logo.jpg'),
        ])->assertSessionHasErrors(['app_android_file', 'app_ios_url', 'app_android_url', 'app_color', 'app_link_color', 'app_logo']);
    }

    public function test_a_failed_save_leaves_no_uploaded_files_or_media_behind(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser('admin');
        $business = Business::factory()->create(['slug' => 'paydar-fund', 'app_android_path' => 'apps/paydar-fund-current.apk']);
        Storage::disk('public')->put('apps/paydar-fund-current.apk', 'apk');

        // The database rejects the row after the uploads were stored.
        Business::saving(fn () => throw new \RuntimeException('database refused the row'));

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->put("/admin/businesses/{$business->id}", [
                'title' => 'پایدار فاند', 'slug' => 'paydar-fund', 'accent' => 'fund', 'status' => 'draft', 'app_enabled' => 1, 'app_name' => 'پایدار فاند',
                'app_logo' => UploadedFile::fake()->image('logo.png', 120, 160),
                'app_qr' => UploadedFile::fake()->image('qr.png', 400, 400),
                'app_android_file' => UploadedFile::fake()->create('next.apk', 1024, 'application/zip'),
            ]);
            $this->fail('the save should have thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame('database refused the row', $e->getMessage());
        } finally {
            Business::flushEventListeners();
        }

        $this->assertSame(0, Media::count());
        $this->assertSame([], Storage::disk('public')->allFiles('media'));
        $this->assertSame(['apps/paydar-fund-current.apk'], Storage::disk('public')->allFiles('apps'));
        $this->assertSame('apps/paydar-fund-current.apk', $business->fresh()->app_android_path);
    }

    public function test_validation_rejects_bad_accent_slug_url_and_file(): void
    {
        $admin = $this->adminUser('admin');
        Business::factory()->create(['slug' => 'taken']);

        $this->actingAs($admin)->post('/admin/businesses', [
            'title' => 'x', 'slug' => 'taken', 'accent' => 'purple', 'website_url' => 'not-a-url', 'status' => 'draft',
            'image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors(['slug', 'accent', 'website_url', 'image']);
    }

    public function test_editor_manages_but_cannot_publish_and_viewer_permissions_gate_access(): void
    {
        $editor = $this->adminUser('editor');

        $this->actingAs($editor)->get('/admin/businesses')->assertOk();
        $this->actingAs($editor)->post('/admin/businesses', ['title' => 'پیش‌نویس', 'accent' => 'ai', 'status' => 'draft'])->assertRedirect();
        $this->assertDatabaseHas('businesses', ['title' => 'پیش‌نویس']);

        $this->actingAs($editor)->post('/admin/businesses', ['title' => 'منتشر', 'accent' => 'ai', 'status' => 'published'])->assertForbidden();

        $business = Business::firstOrFail();
        $this->actingAs($editor)->delete("/admin/businesses/{$business->id}")->assertRedirect();

        auth()->logout();
        $this->get('/admin/businesses')->assertRedirect('/admin/login');
    }
}
