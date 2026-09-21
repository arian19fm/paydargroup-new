<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fills created_by / updated_by from the authenticated user. Lightweight by
 * design — a full audit log is a later concern.
 */
trait HasAuditFields
{
    public static function bootHasAuditFields(): void
    {
        static::creating(function ($model) {
            $userId = auth()->id();

            $model->created_by ??= $userId;
            $model->updated_by ??= $userId;
        });

        static::updating(function ($model) {
            if ($userId = auth()->id()) {
                $model->updated_by = $userId;
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
