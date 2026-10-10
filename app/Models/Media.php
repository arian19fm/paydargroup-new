<?php

namespace App\Models;

use App\Support\Seo\SeoManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A file in the media library. The binary lives on the configured disk;
 * this row holds metadata. alt_text matters for SEO and accessibility.
 */
class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'disk', 'path', 'filename', 'original_filename', 'mime_type', 'extension',
        'size', 'width', 'height', 'alt_text', 'title', 'caption', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Remove the file when the metadata row is deleted.
        static::deleted(function (Media $media) {
            Storage::disk($media->disk)->delete($media->path);
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Public URL of the file on its disk. */
    /**
     * URL for use in pages. Files on a local disk get a root-relative path
     * ("/storage/…") so they load from whatever host serves the page; an
     * absolute URL built from APP_URL breaks as soon as the site is
     * reached on another host or port. Remote disks keep their own URL.
     */
    public function url(): string
    {
        $url = Storage::disk($this->disk)->url($this->path);

        if (config("filesystems.disks.{$this->disk}.driver") !== 'local') {
            return $url;
        }

        return parse_url($url, PHP_URL_PATH) ?: $url;
    }

    /** Absolute URL under APP_URL, for sharing metadata and structured data. */
    public function absoluteUrl(): string
    {
        $url = $this->url();

        return str_starts_with($url, '/') ? SeoManager::absoluteUrl($url) : $url;
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }
}
