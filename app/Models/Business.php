<?php

namespace App\Models;

use App\Contracts\Seoable;
use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\Publishable;
use App\Support\Seo\SeoManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * A group business ("کسب و کار"): a card in the home page products section
 * and its own page at /businesses/{slug}. Only published() businesses are
 * shown anywhere on the site.
 */
class Business extends Model implements Seoable
{
    use HasAuditFields, HasFactory, HasSeo, Publishable, SoftDeletes;

    /** Accent palettes available to a business (keys of $pg-products in SCSS). */
    public const ACCENTS = ['fund', 'exchange', 'broker', 'ai', 'bot'];

    /** Disk and size cap (KB) of an uploaded Android install file. */
    public const APP_DISK = 'public';

    public const APP_MAX_KILOBYTES = 307200;

    protected $fillable = [
        'title', 'slug', 'eyebrow', 'tagline', 'features', 'accent', 'image_media_id', 'excerpt', 'content', 'website_url',
        'app_enabled', 'app_name', 'app_description', 'app_color', 'app_link_color', 'app_android_path', 'app_android_size', 'app_android_url', 'app_ios_url', 'app_logo_media_id', 'app_qr_media_id',
        'benefits_title', 'benefits_text', 'benefits', 'benefits_media_id', 'benefits_poster_media_id', 'status', 'published_at', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'benefits' => 'array',
            'sort_order' => 'integer',
            'app_enabled' => 'boolean',
            'app_android_size' => 'integer',
        ];
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }

    /** Image or video shown in the benefits section. */
    public function benefitsMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'benefits_media_id');
    }

    /** Banner shown over the benefits video before it plays (else the business image). */
    public function benefitsPoster(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'benefits_poster_media_id');
    }

    /** The image to use as the benefits video poster, if any. */
    public function benefitsPosterImage(): ?Media
    {
        $poster = $this->benefitsPoster;

        if ($poster?->isImage()) {
            return $poster;
        }

        return $this->image?->isImage() ? $this->image : null;
    }

    /** App logo: the white tile on the download card and, enlarged, the cover watermark. */
    public function appLogo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'app_logo_media_id');
    }

    /** QR code shown on the download card's cover (desktop). */
    public function appQr(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'app_qr_media_id');
    }

    /** Businesses listed on the download page: switched on and with something to install. */
    public function scopeWithApp(Builder $query): Builder
    {
        return $query->where('app_enabled', true)->where(fn (Builder $q) => $q
            ->whereNotNull('app_android_path')
            ->orWhereNotNull('app_android_url')
            ->orWhereNotNull('app_ios_url'));
    }

    /** Where the Android button points: the uploaded file first, else the link. */
    public function androidUrl(): ?string
    {
        if ($this->app_android_path) {
            $url = Storage::disk(self::APP_DISK)->url($this->app_android_path);

            return parse_url($url, PHP_URL_PATH) ?: $url;
        }

        return $this->app_android_url;
    }

    /** True when the Android button downloads the uploaded file (not an outside link). */
    public function androidIsFile(): bool
    {
        return (bool) $this->app_android_path;
    }

    /** Uploaded file size for display, e.g. "24.5" (megabytes); null without a file. */
    public function androidSizeInMegabytes(): ?float
    {
        return $this->app_android_path && $this->app_android_size ? round($this->app_android_size / 1048576, 1) : null;
    }

    /** Name on the download card: the app's own name, else the business title. */
    public function appTitle(): string
    {
        return $this->app_name ?: $this->title;
    }

    /** Text of the download card: its own description, else the business excerpt. */
    public function appText(): ?string
    {
        return $this->app_description ?: $this->excerpt;
    }

    /** Host of the business website as shown on the card ("paydar.fund"). */
    public function websiteHost(): ?string
    {
        $host = $this->website_url ? parse_url($this->website_url, PHP_URL_HOST) : null;

        return $host ? preg_replace('/^www\./i', '', $host) : null;
    }

    /**
     * Inline custom properties of the card: cover colour and link colour
     * (the link falls back to the cover). Values are validated hex colours;
     * blank leaves the stylesheet's brand default in place.
     */
    public function appStyle(): ?string
    {
        $hex = fn (?string $value) => $value && preg_match('/^#[0-9a-f]{6}$/i', $value) ? strtolower($value) : null;
        $cover = $hex($this->app_color);
        $link = $hex($this->app_link_color) ?? $cover;

        return implode(' ', array_filter([
            $cover ? "--pg-app-color: {$cover};" : null,
            $link ? "--pg-app-link: {$link};" : null,
        ])) ?: null;
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** Feature badges as a clean list of strings (blank lines dropped). */
    public function featureList(): array
    {
        return array_values(array_filter(array_map('trim', $this->features ?? []), fn (string $f) => $f !== ''));
    }

    /**
     * Benefits as [title, description] pairs (rows without a title dropped).
     *
     * @return list<array{title: string, description: string}>
     */
    public function benefitList(): array
    {
        $rows = [];

        foreach ($this->benefits ?? [] as $row) {
            $title = trim((string) ($row['title'] ?? ''));

            if ($title !== '') {
                $rows[] = ['title' => $title, 'description' => trim((string) ($row['description'] ?? ''))];
            }
        }

        return $rows;
    }

    /** Benefits as form text: one per line, "title | description". */
    public function benefitsAsText(): string
    {
        return implode(PHP_EOL, array_map(
            fn (array $b) => $b['description'] === '' ? $b['title'] : $b['title'].' | '.$b['description'],
            $this->benefitList(),
        ));
    }

    /** Parse the form text back into rows — the inverse of benefitsAsText(). */
    public static function parseBenefits(string $text): array
    {
        $rows = [];

        foreach (preg_split('/\R/u', $text) as $line) {
            [$title, $description] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');

            if ($title !== '') {
                $rows[] = ['title' => $title, 'description' => $description];
            }
        }

        return $rows;
    }

    /** The accent key, falling back to the first palette when the stored one is unknown. */
    public function accentKey(): string
    {
        return in_array($this->accent, self::ACCENTS, true) ? $this->accent : self::ACCENTS[0];
    }

    // ---------------------------------------------------------------- Seoable

    public function seoDefaultTitle(): string
    {
        return $this->title;
    }

    public function seoDefaultDescription(): ?string
    {
        return $this->excerpt ?: $this->tagline;
    }

    public function seoDefaultImage(): ?Media
    {
        return $this->image;
    }

    public function seoOgType(): string
    {
        return 'website';
    }

    public function publicUrl(): ?string
    {
        return SeoManager::absoluteUrl(route('businesses.show', $this->slug, absolute: false));
    }
}
