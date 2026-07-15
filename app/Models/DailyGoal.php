<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyGoal extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'goal_minutes',
        'goal_type',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'goal_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
