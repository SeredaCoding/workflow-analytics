<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyGoal extends Model
{
    protected $fillable = [
        'date',
        'goal_minutes',
        'goal_type',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
