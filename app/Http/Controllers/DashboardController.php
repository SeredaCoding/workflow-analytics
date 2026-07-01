<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();
        $userId = auth()->id();

        $activitiesToday = Activity::where('user_id', $userId)->whereDate('started_at', $today)->get();

        $totalMinutes = $activitiesToday->sum('duration_minutes');
        $interruptions = $activitiesToday->where('type', 'interruption');
        $development = $activitiesToday->whereIn('type', ['activity'])->filter(fn($a) => $a->category?->type === 'development');
        $support = $activitiesToday->whereIn('type', ['activity'])->filter(fn($a) => $a->category?->type === 'support');
        $meetings = $activitiesToday->whereIn('type', ['activity'])->filter(fn($a) => $a->category?->type === 'meeting');

        $inProgress = Activity::where('user_id', $userId)->inProgress()->latest('started_at')->with(['category', 'project'])->first();

        $threeDaysAgo = today()->subDays(2);

        $timeline = Activity::where('user_id', $userId)->whereDate('started_at', '>=', $threeDaysAgo)
            ->with(['category', 'project'])
            ->orderBy('started_at', 'desc')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'type' => $a->type,
                'parent_id' => $a->parent_id,
                'category' => $a->category?->name,
                'category_color' => $a->category?->color,
                'project' => $a->project?->name,
                'started_at' => $a->started_at->toIso8601String(),
                'ended_at' => $a->ended_at?->toIso8601String(),
                'duration' => $a->duration_minutes,
                'status' => $a->status,
                'date' => $a->started_at->format('Y-m-d'),
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_minutes' => $totalMinutes,
                'total_hours' => round($totalMinutes / 60, 1),
                'interruptions_count' => $interruptions->count(),
                'development_minutes' => $development->sum('duration_minutes'),
                'support_minutes' => $support->sum('duration_minutes'),
                'meeting_minutes' => $meetings->sum('duration_minutes'),
            ],
            'inProgress' => $inProgress ? [
                'id' => $inProgress->id,
                'title' => $inProgress->title,
                'type' => $inProgress->type,
                'parent_id' => $inProgress->parent_id,
                'category' => $inProgress->category?->name,
                'category_color' => $inProgress->category?->color,
                'project' => $inProgress->project?->name,
                'started_at' => $inProgress->started_at->toIso8601String(),
            ] : null,
            'timeline' => $timeline,
        ]);
    }
}
