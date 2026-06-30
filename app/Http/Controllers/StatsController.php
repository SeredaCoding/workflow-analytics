<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatsController extends Controller
{
    public function index()
    {
        return Inertia::render('Stats', [
            'monthly' => $this->monthlyData(),
            'categoryDistribution' => $this->categoryDistribution(),
        ]);
    }

    public function daily()
    {
        $days = collect(range(6, 0))->map(function ($i) {
            $date = today()->subDays($i);
            $activities = Activity::whereDate('started_at', $date)->get();

            return [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'total_minutes' => $activities->sum('duration_minutes'),
                'interruptions' => $activities->where('type', 'interruption')->count(),
                'development_minutes' => $activities->filter(fn($a) => $a->category?->type === 'development')->sum('duration_minutes'),
                'support_minutes' => $activities->filter(fn($a) => $a->category?->type === 'support')->sum('duration_minutes'),
            ];
        });

        return response()->json($days);
    }

    public function monthlyData()
    {
        $startOfMonth = now()->startOfMonth();
        $activities = Activity::where('started_at', '>=', $startOfMonth)->get();

        return [
            'total_minutes' => $activities->sum('duration_minutes'),
            'total_hours' => round($activities->sum('duration_minutes') / 60, 1),
            'interruptions' => $activities->where('type', 'interruption')->count(),
            'development_minutes' => $activities->filter(fn($a) => $a->category?->type === 'development')->sum('duration_minutes'),
            'support_minutes' => $activities->filter(fn($a) => $a->category?->type === 'support')->sum('duration_minutes'),
            'meeting_minutes' => $activities->filter(fn($a) => $a->category?->type === 'meeting')->sum('duration_minutes'),
            'avg_focus_minutes' => round($activities->where('type', 'activity')->whereNotNull('duration_minutes')->avg('duration_minutes') ?? 0),
        ];
    }

    public function categoryDistribution()
    {
        $startOfMonth = now()->startOfMonth();
        $activities = Activity::where('started_at', '>=', $startOfMonth)
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(function ($items, $category) {
                return [
                    'name' => $category ?: 'Sem categoria',
                    'minutes' => $items->sum('duration_minutes'),
                    'count' => $items->count(),
                ];
            })
            ->values();

        return $activities;
    }

    public function heatmap()
    {
        $days = collect(range(0, 364))->map(function ($i) {
            $date = today()->subDays(364 - $i);
            $minutes = Activity::whereDate('started_at', $date)->sum('duration_minutes');

            return [
                'date' => $date->format('Y-m-d'),
                'count' => (int) $minutes,
            ];
        });

        return response()->json($days);
    }
}
