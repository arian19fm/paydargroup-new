<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One person on the team page: name, role, optional photo and LinkedIn. */
class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = ['team_group_id', 'name', 'role', 'linkedin_url', 'photo_media_id', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TeamGroup::class, 'team_group_id');
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_media_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
