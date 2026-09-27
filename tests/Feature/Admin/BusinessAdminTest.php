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
