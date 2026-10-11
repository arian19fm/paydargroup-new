<?php

namespace Tests\Feature\Admin;

use App\Models\Article;
use App\Models\Business;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class RichContentTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_article_html_is_sanitised_on_save_and_rendered_on_the_site(): void
    {
        $admin = $this->adminUser('admin');

        $this->actingAs($admin)->get('/admin/articles/create')->assertOk()->assertSee('data-rich-editor', false);

        $this->actingAs($admin)->post('/admin/articles', [
            'title' => 'راهنمای HTML', 'slug' => 'html-guide', 'status' => 'published',
            'content' => '<h2 class="intro">مقدمه</h2><p style="color:red">متن <strong>مهم</strong></p>'
                .'<figure><img src="/storage/a.webp" alt="نمودار" width="800" height="450" onerror="x()"><figcaption>شرح</figcaption></figure>'
                .'<table><tr><th>الف</th></tr></table><iframe src="https://example.com"></iframe>',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $stored = Article::firstOrFail()->content;
        $this->assertStringNotContainsString('style=', $stored);
        $this->assertStringNotContainsString('onerror', $stored);
        $this->assertStringNotContainsString('iframe', $stored);
        $this->assertStringContainsString('<h2 class="intro">مقدمه</h2>', $stored);

        $html = $this->get('/articles/html-guide')->assertOk()->getContent();
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringContainsString('<h2 class="intro" id="section-1">مقدمه</h2>', $html);
        $this->assertStringContainsString('<a href="#section-1">مقدمه</a>', $html);
        $this->assertStringContainsString('alt="نمودار" width="800" height="450" loading="lazy" decoding="async"', $html);
        $this->assertStringContainsString('<div class="pg-content__table"><table>', $html);
    }

    public function test_empty_editor_markup_fails_required_content(): void
    {
        $this->actingAs($this->adminUser('admin'))->post('/admin/articles', [
            'title' => 'خالی', 'status' => 'draft', 'content' => '<p></p><p><br></p>',
        ])->assertSessionHasErrors('content');
    }

    public function test_business_body_headings_start_below_the_section_heading(): void
    {
        Business::factory()->published()->create(['slug' => 'demo', 'content' => '<h2>چرا ما؟</h2><p>متن</p>']);

        $this->get('/businesses/demo')->assertOk()->assertSee('<h3>چرا ما؟</h3><p>متن</p>', false);
    }

    public function test_editor_image_upload_goes_to_the_media_library(): void
    {
        Storage::fake('public');
        $editor = $this->adminUser('editor');

        $response = $this->actingAs($editor)->postJson('/admin/media/editor-image', [
            'file' => UploadedFile::fake()->image('chart.png', 1500, 900), 'alt_text' => 'نمودار رشد',
        ])->assertCreated()->assertJson(['width' => 750, 'height' => 450, 'alt' => 'نمودار رشد']);

        $media = Media::findOrFail($response->json('id'));
        $this->assertSame($media->url(), $response->json('url'));
        $this->assertSame(['image/webp', 'webp'], [$media->mime_type, $media->extension]);
        $this->assertStringEndsWith('.webp', $media->path);
        $this->assertSame('image/webp', getimagesizefromstring(Storage::disk('public')->get($media->path))['mime']);

        // Smaller images are converted but never enlarged.
        $this->actingAs($editor)->postJson('/admin/media/editor-image', ['file' => UploadedFile::fake()->image('icon.jpg', 320, 200)])
            ->assertCreated()->assertJson(['width' => 320, 'height' => 200]);

        $this->actingAs($editor)->postJson('/admin/media/editor-image', [
            'file' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ])->assertUnprocessable()->assertJsonValidationErrors('file');

        auth()->logout();
        $this->postJson('/admin/media/editor-image', ['file' => UploadedFile::fake()->image('x.png')])->assertUnauthorized();
    }
}
