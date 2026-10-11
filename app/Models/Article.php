<?php

namespace App\Models;

use App\Contracts\Seoable;
use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\Publishable;
use App\Support\Seo\SeoManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Article / news item. Public URL: /articles/{slug}.
 */
class Article extends Model implements Seoable
{
    use HasAuditFields, HasFactory, HasSeo, Publishable, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'featured_image_id', 'status', 'published_at', 'author_id',
        'news_run_id', 'sources',
    ];

    protected function casts(): array
    {
        return [
            // [{title, url}] — what an automatically written item was based on.
            'sources' => 'array',
        ];
    }

    /** The automatic-news run that wrote this item, if any. */
    public function newsRun(): BelongsTo
    {
        return $this->belongsTo(NewsRun::class);
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ArticleCategory::class, 'article_category');
    }

    /**
     * Estimated reading time in whole minutes, derived from the body's text
     * word count at the configured reading speed (never below one minute).
     */
    public function readingTimeMinutes(): int
    {
        $words = preg_split('/\s+/u', trim(strip_tags(str_replace('<', ' <', (string) $this->content))), -1, PREG_SPLIT_NO_EMPTY);
        $perMinute = max(1, (int) config('home.reading_words_per_minute', 200));

        return max(1, (int) ceil(count($words) / $perMinute));
    }

    // ---------------------------------------------------------------- Seoable

    public function seoDefaultTitle(): string
    {
        return $this->title;
    }

    public function seoDefaultDescription(): ?string
    {
        return $this->excerpt;
    }

    public function seoDefaultImage(): ?Media
    {
        return $this->featuredImage;
    }

    public function seoOgType(): string
    {
        return 'article';
    }

    public function publicUrl(): ?string
    {
        return SeoManager::absoluteUrl(route('articles.show', $this->slug, absolute: false));
    }
}
