<?php

namespace App\Contracts;

use App\Models\Media;
use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * A model that can be described in the <head>. Implemented by managed
 * content (Page, Article, ...) through the HasSeo trait plus a handful of
 * fallback methods. SeoManager::fromModel() reads these.
 */
interface Seoable
{
    /** The stored SEO overrides (may be null when nothing was customised). */
    public function seo(): MorphOne;

    /** Fallback title when no SEO title override exists. */
    public function seoDefaultTitle(): string;

    /** Fallback description (e.g. the excerpt); null omits the tag. */
    public function seoDefaultDescription(): ?string;

    /** Fallback sharing image; null falls back to the site default. */
    public function seoDefaultImage(): ?Media;

    /** Open Graph object type for this model: website, article, ... */
    public function seoOgType(): string;

    /** Absolute canonical URL of the public page, or null if it has none. */
    public function publicUrl(): ?string;

    /** Convenience accessor for the loaded relation. */
    public function seoMeta(): ?SeoMeta;
}
