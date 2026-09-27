<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A navigation entry linking to a Page (page_id) or an explicit URL — never
 * both (enforced by MenuItemRequest). An item may also carry a `source`:
 * its children are then generated from managed content (see
 * App\Support\Menus\MenuRepository) and its own link may be left empty,
 * in which case the source's default URL is used.
 */
class MenuItem extends Model
{
    use HasFactory;

    public const TARGETS = ['_self', '_blank'];

    /** Automatic child sources: the published businesses. */
    public const SOURCES = ['businesses'];

    protected $fillable = ['menu_id', 'parent_id', 'key', 'label', 'url', 'page_id', 'source', 'target', 'sort_order', 'is_active'];

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

    public function hasSource(): bool
    {
        return in_array($this->source, self::SOURCES, true);
    }

    /**
     * The CMS page slug a site-relative URL such as "/about" points at, or
     * null when the URL is anything else (an application route, an anchor,
     * an external link). Lets the menu hide links to pages that do not
     * exist or are not published yet, exactly like page_id items.
     */
    public function linkedPageSlug(): ?string
    {
        if ($this->page_id || ! $this->url || ! preg_match('#^/([a-z0-9]+(?:-[a-z0-9]+)*)/?$#', $this->url, $m)) {
            return null;
        }

        return in_array($m[1], config('cms.reserved_slugs', []), true) ? null : $m[1];
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

        if ($this->url) {
            return $this->url;
        }

        return $this->hasSource() ? self::sourceUrl($this->source) : null;
    }

    /** Default link of a source item that has no page or URL of its own. */
    public static function sourceUrl(string $source): ?string
    {
        return match ($source) {
            'businesses' => route('home', absolute: false).'#products',
            default => null,
        };
    }
}
