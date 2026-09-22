<?php

namespace App\Http\Requests\Admin;

use App\Models\Media;
use App\Support\Media\MediaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\File;

/**
 * Upload validation: real MIME sniffing (not the client-supplied type),
 * an explicit allow-list (no SVG, no executables, no archives) and a size cap.
 */
class MediaStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Media::class);
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                File::types(MediaService::ALLOWED_EXTENSIONS)->max(MediaService::MAX_VIDEO_KILOBYTES),
                'mimetypes:'.implode(',', MediaService::ALLOWED_MIMES),
                // Only videos may use the larger cap; everything else keeps the 10 MB limit.
                function (string $attribute, mixed $file, \Closure $fail): void {
                    if ($file instanceof UploadedFile
                        && ! str_starts_with((string) $file->getMimeType(), 'video/')
                        && $file->getSize() > MediaService::MAX_KILOBYTES * 1024) {
                        $fail(__('validation.max.file', ['attribute' => $attribute, 'max' => MediaService::MAX_KILOBYTES]));
                    }
                },
            ],
            'alt_text' => ['nullable', 'string', 'max:500'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
