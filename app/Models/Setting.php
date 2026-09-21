<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One typed setting value. Read/write through App\Support\Settings\Settings,
 * never directly, so the cache stays consistent.
 */
class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'is_public'];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }
}
