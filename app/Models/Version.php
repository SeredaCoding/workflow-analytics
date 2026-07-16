<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $fillable = [
        'version', 'release_date', 'description', 'changes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'changes' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
