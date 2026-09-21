<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use App\Support\Redirects\RedirectResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasAuditFields, HasFactory;

    public const STATUS_CODES = [301, 302];

    protected $fillable = ['source_path', 'destination_url', 'http_status', 'is_active'];

    protected function casts(): array
    {
        return [
            'http_status' => 'integer',
            'is_active' => 'boolean',
            'hit_count' => 'integer',
            'last_hit_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Keep the per-path cache honest whenever a rule changes.
        static::saved(function (Redirect $redirect) {
            app(RedirectResolver::class)->forget($redirect->source_path);

            if ($redirect->wasChanged('source_path') && $redirect->getOriginal('source_path')) {
                app(RedirectResolver::class)->forget($redirect->getOriginal('source_path'));
            }
        });

        static::deleted(fn (Redirect $redirect) => app(RedirectResolver::class)->forget($redirect->source_path));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
