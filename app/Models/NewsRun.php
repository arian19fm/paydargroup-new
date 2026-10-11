<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One run of the automatic news writer (App\Support\News). */
class NewsRun extends Model
{
    public const QUEUED = 'queued';

    public const RUNNING = 'running';

    public const SUCCEEDED = 'succeeded';

    public const FAILED = 'failed';

    protected $fillable = [
        'trigger', 'status', 'requested_by', 'started_at', 'finished_at', 'articles_count',
        'model', 'input_tokens', 'output_tokens', 'web_searches', 'error',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::QUEUED, self::RUNNING]);
    }

    /** Rough US$ cost from config('news.pricing'), for the admin list. */
    public function estimatedCost(): float
    {
        $price = config('news.pricing');

        return $this->input_tokens / 1e6 * $price['input']
            + $this->output_tokens / 1e6 * $price['output']
            + $this->web_searches / 1000 * $price['searches'];
    }
}
