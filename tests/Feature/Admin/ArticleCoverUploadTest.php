<?php

namespace Tests\Feature\Admin;

use App\Models\Article;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class ArticleCoverUploadTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_cover_is_uploaded_from_the_article_form_and_can_be_removed(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser('admin');

        $this->actingAs($admin)->post('/admin/articles', [
            'title' => 'مقالهٔ تازه', 'content' => "# عنوان\nمتن", 'status' => 'draft',
            'featured_image' => UploadedFile::fake()->image('cover.jpg', 1294, 391),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $article = Article::firstOrFail();
        $cover = Media::findOrFail($article->featured_image_id);
        $this->assertSame('مقالهٔ تازه', $cover->title);
        Storage::disk('public')->assertExists($cover->path);

        $this->actingAs($admin)->get("/admin/articles/{$article->id}/edit")->assertOk()->assertSee('remove_featured_image');

        $this->actingAs($admin)->put("/admin/articles/{$article->id}", [
            'title' => 'مقالهٔ تازه', 'slug' => $article->slug, 'content' => 'متن', 'status' => 'draft',
            'featured_image_id' => $cover->id, 'remove_featured_image' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull($article->fresh()->featured_image_id);

        $this->actingAs($admin)->post('/admin/articles', [
            'title' => 'x', 'content' => 'y', 'status' => 'draft', 'featured_image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('featured_image');
    }
}
