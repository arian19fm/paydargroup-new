<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    public const LOCATIONS = ['main', 'footer'];

    protected $fillable = ['name', 'location'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    /** Top-level items with their children, ordered. */
    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id')->with('children.page', 'page');
    }
}
