<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** A résumé submitted for an opening. The file is on the private disk. */
class JobApplication extends Model
{
    use HasFactory;

    public const DISK = 'local';

    protected $fillable = [
        'job_opening_id', 'job_title', 'phone', 'resume_path', 'resume_name', 'resume_mime', 'resume_size', 'ip', 'user_agent', 'seen_at',
    ];

    protected static function booted(): void
    {
        static::deleted(function (JobApplication $application) {
            Storage::disk(self::DISK)->delete($application->resume_path);
        });
    }

    protected function casts(): array
    {
        return ['seen_at' => 'datetime', 'resume_size' => 'integer'];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class, 'job_opening_id');
    }

    public function scopeUnseen(Builder $query): Builder
    {
        return $query->whereNull('seen_at');
    }

    public function isSeen(): bool
    {
        return $this->seen_at !== null;
    }

    public function markSeen(): void
    {
        if (! $this->isSeen()) {
            $this->forceFill(['seen_at' => now()])->save();
        }
    }

    /** Unseen applications, for the admin badge (0 when the table is missing). */
    public static function unseenCount(): int
    {
        try {
            return static::query()->unseen()->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
