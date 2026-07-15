<?php

namespace App\Models;

use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProblemReport extends Model
{
    use HasImages;

    protected $fillable = [
        'user_id', 'url', 'page_name', 'description',
        'severity', 'app_version', 'browser_info', 'images',
        'status', 'resolution_notes', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'browser_info' => 'array',
            'images' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
