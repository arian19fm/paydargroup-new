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
    ];

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
