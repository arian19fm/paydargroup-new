<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A navigation entry linking to a Page (page_id) or an explicit URL — never
 * both, never neither (enforced by MenuItemRequest).
 */
class MenuItem extends Model
{
    use HasFactory;

    public const TARGETS = ['_self', '_blank'];

    protected $fillable = ['menu_id', 'parent_id', 'label', 'url', 'page_id', 'target', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The URL to render, or null when the item currently has no valid
     * target (e.g. its page is unpublished) and should be hidden.
     */
    public function resolvedUrl(): ?string
    {
        if ($this->page_id) {
            $page = $this->page;

            return $page && $page->isPublished() ? $page->publicUrl() : null;
        }

        return $this->url;
    }
}
