<?php

namespace App\Models\Concerns;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Attaches the polymorphic seo_meta row. Models using this trait must
 * implement App\Contracts\Seoable's fallback methods.
 */
trait HasSeo
{
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function seoMeta(): ?SeoMeta
    {
        return $this->seo;
    }

    /**
     * Persist the SEO override fields from a validated form payload. Blank
     * values are stored as null so fallbacks apply; an all-empty payload
     * on a model without a row creates nothing.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveSeo(array $data): void
    {
        $attributes = SeoMeta::normalize($data);

        if ($this->seo === null && SeoMeta::isEmpty($attributes)) {
            return;
        }

        $this->seo()->updateOrCreate([], $attributes);
        $this->unsetRelation('seo');
    }

    /** Content whose SEO row does not opt out of indexing. */
    public function scopeIndexable(Builder $query): Builder
    {
        return $query->whereDoesntHave('seo', fn (Builder $seo) => $seo->where('robots_index', false));
    }
}
