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

    protected $fillable = [
        'title', 'slug', 'eyebrow', 'tagline', 'features', 'accent', 'image_media_id', 'excerpt', 'content', 'website_url',
        'benefits_title', 'benefits_text', 'benefits', 'benefits_media_id', 'benefits_poster_media_id', 'status', 'published_at', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'benefits' => 'array',
            'sort_order' => 'integer',
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
