<?php

namespace App\Support\Media;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores uploads on a filesystem disk and records their metadata. Files are
 * renamed to random names under media/YYYY/MM so original names never hit
 * the filesystem. Validation (MIME/size) happens in MediaStoreRequest.
 *
 * uploadContentImage() is for images shown inside content (article covers,
 * editor images): they are stored as WebP scaled down to
 * config('cms.content_image.width').
 */
class MediaService
{
    public const ALLOWED_MIMES = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf',
        'video/mp4', 'video/webm',
    ];

    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'mp4', 'webm'];

    public const VIDEO_EXTENSIONS = ['mp4', 'webm'];

    /** Size caps: images/documents, and (larger) background videos. */
    public const MAX_KILOBYTES = 10240;

    public const MAX_VIDEO_KILOBYTES = 61440;

    public function __construct(protected string $disk = 'public') {}

    public function upload(UploadedFile $file, ?User $user = null, array $attributes = []): Media
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $filename = Str::uuid().'.'.$extension;

        $path = $file->storeAs($this->directory(), $filename, ['disk' => $this->disk]);

        [$width, $height] = $this->dimensions($file);

        return $this->record($file, $user, $attributes, [
            'path' => $path,
            'filename' => $filename,
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'extension' => $extension,
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
        ]);
    }

    /**
     * An image for use inside content, re-encoded as WebP and scaled down
     * to the configured width (smaller images keep their size). GIFs and
     * files GD cannot convert are stored unchanged.
     */
    public function uploadContentImage(UploadedFile $file, ?User $user = null, array $attributes = []): Media
    {
        $webp = WebpImage::encode(
            $file->getRealPath(),
            (int) config('cms.content_image.width', 750),
            (int) config('cms.content_image.quality', 82),
        );

        if ($webp === null) {
            return $this->upload($file, $user, $attributes);
        }

        $filename = Str::uuid().'.webp';
        $path = $this->directory().'/'.$filename;
        Storage::disk($this->disk)->put($path, $webp['data']);

        return $this->record($file, $user, $attributes, [
            'path' => $path,
            'filename' => $filename,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => strlen($webp['data']),
            'width' => $webp['width'],
            'height' => $webp['height'],
        ]);
    }

    protected function directory(): string
    {
        return 'media/'.now()->format('Y/m');
    }

    protected function record(UploadedFile $file, ?User $user, array $attributes, array $stored): Media
    {
        return Media::create(array_merge([
            'disk' => $this->disk,
            'original_filename' => Str::limit($file->getClientOriginalName(), 250, ''),
            'uploaded_by' => $user?->id,
        ], $stored, array_intersect_key($attributes, array_flip(['alt_text', 'title', 'caption']))));
    }

    /** @return array{0: ?int, 1: ?int} */
    protected function dimensions(UploadedFile $file): array
    {
        if (! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return [null, null];
        }

        $info = @getimagesize($file->getRealPath());

        return $info ? [$info[0], $info[1]] : [null, null];
    }
}
