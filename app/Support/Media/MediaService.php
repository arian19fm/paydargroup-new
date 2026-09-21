<?php

namespace App\Support\Media;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Stores uploads on a filesystem disk and records their metadata. Files are
 * renamed to random names under media/YYYY/MM so original names never hit
 * the filesystem. Validation (MIME/size) happens in MediaStoreRequest.
 */
class MediaService
{
    public const ALLOWED_MIMES = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf',
    ];

    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'];

    public const MAX_KILOBYTES = 10240;

    public function __construct(protected string $disk = 'public') {}

    public function upload(UploadedFile $file, ?User $user = null, array $attributes = []): Media
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $filename = Str::uuid().'.'.$extension;
        $directory = 'media/'.now()->format('Y/m');

        $path = $file->storeAs($directory, $filename, ['disk' => $this->disk]);

        [$width, $height] = $this->dimensions($file);

        return Media::create(array_merge([
            'disk' => $this->disk,
            'path' => $path,
            'filename' => $filename,
            'original_filename' => Str::limit($file->getClientOriginalName(), 250, ''),
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'extension' => $extension,
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'uploaded_by' => $user?->id,
        ], array_intersect_key($attributes, array_flip(['alt_text', 'title', 'caption']))));
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
