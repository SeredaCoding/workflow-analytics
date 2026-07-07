<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatsController extends Controller
{
    public function __construct(
        private ReportService $reportService,
    ) {}

    public function index(Request $request)
    {
        $date = $this->parseMonth($request->input('month'));
        $userId = auth()->id();

        $monthly = $this->reportService->monthlyData($userId, true, $date);
        $categoryDistribution = $this->reportService->categoryDistribution($userId, true, $date);
        $dailyBreakdown = $this->reportService->dailyBreakdown($userId, true, $date);
        $topActivities = $this->reportService->topActivities($userId, true, $date);
        $projectDistribution = $this->reportService->projectDistribution($userId, true, $date);
        $contextDistribution = $this->reportService->contextDistribution($userId, true, $date);

        $totalMinutes = $monthly['total_minutes'] ?: 1;

        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();
        $hasPrev = Activity::where('user_id', $userId)
            ->where('started_at', '<', $monthStart)->exists();
        $hasNext = Activity::where('user_id', $userId)
            ->where('started_at', '>', $monthEnd)->exists();

        $inProgress = Activity::where('user_id', $userId)->inProgress()->latest('started_at')->with(['category', 'project', 'context'])->first();

        return Inertia::render('Stats', [
            'month' => $date->format('Y-m'),
            'monthly' => $monthly,
            'categoryDistribution' => $categoryDistribution,
            'dailyBreakdown' => $dailyBreakdown,
            'topActivities' => $topActivities,
            'totalMinutes' => $totalMinutes,
            'projectDistribution' => $projectDistribution,
            'contextDistribution' => $contextDistribution,
            'hasPrev' => $hasPrev,
            'hasNext' => $hasNext,
            'inProgress' => $inProgress ? [
                'id' => $inProgress->id,
                'title' => $inProgress->title,
                'type' => $inProgress->type,
                'parent_id' => $inProgress->parent_id,
                'category' => $inProgress->category?->name,
                'category_color' => $inProgress->category?->color,
                'project' => $inProgress->project?->name,
                'context' => $inProgress->context?->name,
                'started_at' => $inProgress->started_at->toIso8601String(),
            ] : null,
        ]);
    }

    public function yearly()
    {
        $userId = auth()->id();

        $months = collect(range(0, 11))->map(function ($i) use ($userId) {
            $date = now()->subMonths(11 - $i);
            $data = $this->reportService->monthlyData($userId, false, $date);
            return [
                'month' => $date->translatedFormat('M/Y'),
                'total_minutes' => $data['total_minutes'],
                'total_hours' => $data['total_hours'],
                'interruptions' => $data['interruptions'],
            ];
        });

        return response()->json($months);
    }

    private function parseMonth(?string $month): Carbon
    {
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            return Carbon::parse($month . '-01');
        }
        return now();
    }

    public function daily(Request $request)
    {
        $userId = auth()->id();
        $ref = $this->parseMonth($request->input('month'))->copy()->endOfMonth()->min(now());
        $days = collect(range(6, 0))->map(function ($i) use ($ref, $userId) {
            $date = $ref->copy()->subDays($i);
            $activities = Activity::where('user_id', $userId)
                ->whereDate('started_at', $date)
                ->with('category')
                ->get();

            $activities = $this->reportService->fillInProgressDuration($activities, true);

            $categoryMinutes = $activities
                ->where('type', 'activity')
                ->groupBy(fn($a) => $a->category?->name ?? 'Sem categoria')
                ->map(function ($items) {
                    $first = $items->first();
                    return [
                        'minutes' => $items->sum('duration_minutes'),
                        'color' => $first->category?->color ?? '#6366f1',
                    ];
                });

            return [
                'date' => $date->format('Y-m-d'),
                'label' => $date->translatedFormat('D'),
                'total_minutes' => $activities->sum('duration_minutes'),
                'interruptions' => $activities->where('type', 'interruption')->count(),
                'categories' => $categoryMinutes,
            ];
        });

        return response()->json($days);
    }

    public function heatmap()
    {
        $userId = auth()->id();
        $days = collect(range(0, 364))->map(function ($i) use ($userId) {
            $date = today()->subDays(364 - $i);
            $minutes = Activity::where('user_id', $userId)->whereDate('started_at', $date)->sum('duration_minutes');

            return [
                'date' => $date->format('Y-m-d'),
                'count' => (int) $minutes,
            ];
        });

        return response()->json($days);
    }
}
