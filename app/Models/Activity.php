<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'type', 'category_id', 'project_id', 'parent_id',
        'title', 'description', 'priority', 'source', 'person',
        'tags', 'status', 'started_at', 'ended_at', 'duration_minutes',
        'is_planned', 'energy_level', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'is_planned' => 'boolean',
            'energy_level' => 'integer',
            'duration_minutes' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('started_at', today());
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
}
