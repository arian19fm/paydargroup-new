<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A message sent through the public contact form. Plain storage only:
 * follow-up happens by phone, and an admin listing arrives in a later phase.
 */
class ContactRequest extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'message', 'ip', 'user_agent', 'handled_at'];

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
        ];
    }
}
