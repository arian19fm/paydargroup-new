<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Support\Settings\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class HomeHeroMediaTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_hero_has_no_background_photo_and_no_video_by_default(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('/images/home/hero.', $html);
        $this->assertStringNotContainsString('pg-hero__media', $html);
        $this->assertStringNotContainsString('<video', $html);
    }

    public function test_hero_video_is_a_box_with_controls_and_the_image_as_cover(): void
    {
        $video = Media::factory()->create(['mime_type' => 'video/mp4', 'extension' => 'mp4', 'path' => 'media/2026/09/intro.mp4', 'width' => null, 'height' => null]);
        $poster = Media::factory()->create(['path' => 'media/2026/09/poster.jpg', 'width' => 1920, 'height' => 1080]);
        app(Settings::class)->set('home.hero_video_media_id', $video->id);
        app(Settings::class)->set('home.hero_image_media_id', $poster->id);

        $html = $this->get('/')->getContent();

        // A box at the top of the copy column, above the eyebrow; controls, no autoplay, the image as cover.
        $this->assertMatchesRegularExpression('~<div class="pg-hero__copy">\s*<div class="pg-hero__video-box">\s*<video class="pg-hero__video" controls playsinline preload="metadata"\s+poster="'.preg_quote($poster->url(), '~').'"~', $html);
        $this->assertStringContainsString('aria-label="'.__('home.hero.video_label').'"', $html);
        $this->assertStringNotContainsString('autoplay', $html);
        $this->assertStringContainsString('<source src="'.$video->url().'" type="video/mp4">', $html);
        $this->assertLessThan(strpos($html, 'class="pg-hero__eyebrow"'), strpos($html, 'class="pg-hero__video-box"'));
        // The image is only the cover: no background photo, no preload.
        $this->assertSame(1, substr_count($html, $poster->url()));
        $this->assertStringNotContainsString('rel="preload" as="image"', $html);
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
    }

    public function test_media_urls_are_host_relative_in_pages_and_absolute_for_sharing(): void
    {
        $video = Media::factory()->create(['mime_type' => 'video/mp4', 'extension' => 'mp4', 'path' => 'media/2026/10/intro.mp4', 'width' => null, 'height' => null]);
        app(Settings::class)->set('home.hero_video_media_id', $video->id);

        // Served on any host/port, the page must not point media at APP_URL.
        $this->assertSame('/storage/media/2026/10/intro.mp4', $video->url());
        $this->assertSame(rtrim(config('app.url'), '/').'/storage/media/2026/10/intro.mp4', $video->absoluteUrl());
        $this->assertStringContainsString('<source src="/storage/media/2026/10/intro.mp4" type="video/mp4">', $this->get('/')->getContent());
    }

    public function test_a_non_video_id_in_the_video_setting_is_ignored(): void
    {
        $image = Media::factory()->create();
        app(Settings::class)->set('home.hero_video_media_id', $image->id);
        app(Settings::class)->set('home.hero_image_media_id', 999999);

        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('<video', $html);
        $this->assertStringNotContainsString('/images/home/hero.', $html);
    }

    public function test_admin_can_upload_an_mp4_to_the_media_library(): void
    {
        Storage::fake('public');

        $this->actingAs($this->superAdmin())->post('/admin/media', [
            'file' => UploadedFile::fake()->create('intro.mp4', 20480, 'video/mp4'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $media = Media::firstOrFail();
        $this->assertSame('video/mp4', $media->mime_type);
        $this->assertTrue($media->isVideo());
        $this->assertNull($media->width);
    }

    public function test_images_keep_the_smaller_upload_limit(): void
    {
        Storage::fake('public');

        $this->actingAs($this->superAdmin())->post('/admin/media', [
            'file' => UploadedFile::fake()->create('big.jpg', 20480, 'image/jpeg'),
        ])->assertSessionHasErrors(['file']);
    }

    public function test_home_settings_group_is_editable_in_the_admin(): void
    {
        $video = Media::factory()->create(['mime_type' => 'video/mp4', 'extension' => 'mp4']);
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/admin/settings/home')->assertOk()->assertSee('name="values[hero_video_media_id]"', false);

        $this->actingAs($admin)->put('/admin/settings/home', ['values' => ['hero_video_media_id' => $video->id, 'hero_image_media_id' => '']])
            ->assertRedirect();

        $this->assertSame($video->id, app(Settings::class)->get('home.hero_video_media_id'));
        $this->assertNull(app(Settings::class)->get('home.hero_image_media_id'));
    }
}
