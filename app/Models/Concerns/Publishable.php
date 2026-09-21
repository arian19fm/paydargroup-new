<?php

namespace App\Models\Concerns;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * Draft/published behaviour shared by managed content.
 *
 * "Published" means status = published AND published_at <= now. Drafts and
 * future-dated items never satisfy the published() scope, so they cannot
 * leak to public routes or the sitemap.
 */
trait Publishable
{
    public function initializePublishable(): void
    {
        $this->casts['status'] = ContentStatus::class;
        $this->casts['published_at'] = 'datetime';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ContentStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Draft->value);
    }

    public function isPublished(): bool
    {
        return $this->status === ContentStatus::Published
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    public function isScheduled(): bool
    {
        return $this->status === ContentStatus::Published
            && $this->published_at !== null
            && $this->published_at->gt(now());
    }
}
