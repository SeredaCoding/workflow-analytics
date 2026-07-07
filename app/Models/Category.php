<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'color', 'icon', 'type', 'sort_order', 'is_active', 'visibility',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function sectors(): BelongsToMany
    {
        return $this->belongsToMany(Sector::class, 'category_sector');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'category_user');
    }
}
