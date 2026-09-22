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

    public function test_hero_uses_the_designed_photo_by_default(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('/images/home/hero.webp', $html);
        $this->assertStringNotContainsString('<video', $html);
    }

    public function test_hero_renders_the_admin_video_over_the_poster(): void
    {
        $video = Media::factory()->create(['mime_type' => 'video/mp4', 'extension' => 'mp4', 'path' => 'media/2026/09/intro.mp4', 'width' => null, 'height' => null]);
        $poster = Media::factory()->create(['path' => 'media/2026/09/poster.jpg', 'width' => 1920, 'height' => 1080]);
        app(Settings::class)->set('home.hero_video_media_id', $video->id);
        app(Settings::class)->set('home.hero_image_media_id', $poster->id);

        $html = $this->get('/')->getContent();

        $this->assertMatchesRegularExpression('~<video class="pg-hero__video" autoplay muted loop playsinline[^>]*poster="[^"]*poster\.jpg"~', $html);
        $this->assertStringContainsString('<source src="'.$video->url().'" type="video/mp4">', $html);
        $this->assertStringContainsString('src="'.$poster->url().'" width="1920" height="1080"', $html);
        $this->assertStringContainsString('<link rel="preload" as="image" href="'.$poster->url().'"', $html);
        $this->assertStringNotContainsString('/images/home/hero.webp', $html);
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
    }

    public function test_a_non_video_id_in_the_video_setting_is_ignored(): void
    {
        $image = Media::factory()->create();
        app(Settings::class)->set('home.hero_video_media_id', $image->id);
        app(Settings::class)->set('home.hero_image_media_id', 999999);

        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('<video', $html);
        $this->assertStringContainsString('/images/home/hero.webp', $html);
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
