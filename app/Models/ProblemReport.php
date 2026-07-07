<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProblemReport extends Model
{
    protected $fillable = [
        'user_id', 'url', 'page_name', 'description',
        'severity', 'browser_info', 'status',
    ];

    protected function casts(): array
    {
        return [
            'browser_info' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
