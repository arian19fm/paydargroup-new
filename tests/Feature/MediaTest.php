<?php

namespace Tests\Feature;

use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_valid_image_upload_is_stored_with_metadata(): void
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post('/admin/media', [
            'file' => UploadedFile::fake()->image('office photo.jpg', 800, 600),
            'alt_text' => 'دفتر مرکزی',
        ])->assertRedirect();

        $media = Media::firstOrFail();
        $this->assertSame('public', $media->disk);
        $this->assertSame('image/jpeg', $media->mime_type);
        $this->assertSame([800, 600], [$media->width, $media->height]);
        $this->assertSame('office photo.jpg', $media->original_filename);
        $this->assertSame('دفتر مرکزی', $media->alt_text);
        $this->assertSame($admin->id, $media->uploaded_by);
        $this->assertMatchesRegularExpression('#^media/\d{4}/\d{2}/[0-9a-f-]{36}\.jpg$#', $media->path);
        Storage::disk('public')->assertExists($media->path);

        $this->actingAs($admin)->delete("/admin/media/{$media->id}")->assertRedirect();
        Storage::disk('public')->assertMissing($media->path);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    /**
     * Fake uploads report a MIME type derived from the file name, so content
     * sniffing (finfo on real uploads via the `mimetypes` rule) cannot be
     * exercised here; this covers the extension/type allow-list.
     */
    public function test_disallowed_file_types_are_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $files = [
            UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
            UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;'),
            UploadedFile::fake()->create('archive.zip', 10, 'application/zip'),
        ];

        foreach ($files as $file) {
            $this->actingAs($admin)->from('/admin/media/create')
                ->post('/admin/media', ['file' => $file])
                ->assertSessionHasErrors('file');
        }

        $this->assertSame(0, Media::count());
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_oversized_upload_is_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->superAdmin())->from('/admin/media/create')
            ->post('/admin/media', ['file' => UploadedFile::fake()->image('big.jpg')->size(11000)])
            ->assertSessionHasErrors('file');
    }

    public function test_editor_can_update_media_metadata(): void
    {
        $media = Media::factory()->create();

        $this->actingAs($this->editor())->put("/admin/media/{$media->id}", ['alt_text' => 'جدید', 'title' => 't'])
            ->assertRedirect();

        $this->assertSame('جدید', $media->fresh()->alt_text);
    }
}
