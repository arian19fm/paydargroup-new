<?php

namespace App\Models;

use App\Contracts\Seoable;
use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\Publishable;
use App\Support\Seo\SeoManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Editable corporate page, resolved publicly by slug (routes/web.php).
 */
class Page extends Model implements Seoable
{
    use HasAuditFields, HasFactory, HasSeo, Publishable, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'status', 'published_at', 'template', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
        ];
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
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
        return null;
    }

    public function seoOgType(): string
    {
        return 'website';
    }

    public function publicUrl(): ?string
    {
        return SeoManager::absoluteUrl(route('pages.show', $this->slug, absolute: false));
    }
}
