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

        $monthly = $this->reportService->monthlyData(false, $date);
        $categoryDistribution = $this->reportService->categoryDistribution(false, $date);
        $dailyBreakdown = $this->reportService->dailyBreakdown(false, $date);
        $topActivities = $this->reportService->topActivities(false, $date);

        $totalMinutes = $monthly['total_minutes'] ?: 1;

        $inProgress = Activity::inProgress()->latest('started_at')->with(['category', 'project'])->first();

        return Inertia::render('Stats', [
            'month' => $date->format('Y-m'),
            'monthly' => $monthly,
            'categoryDistribution' => $categoryDistribution,
            'dailyBreakdownHtml' => $this->reportService->buildDailyHtml($dailyBreakdown, true),
            'weeklySummaryHtml' => $this->reportService->buildWeeklyHtml($dailyBreakdown, true, $date),
            'topActivitiesHtml' => $this->reportService->buildTopActivitiesHtml($topActivities, $totalMinutes, true),
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
        ]);
    }

    public function yearly()
    {
        $months = collect(range(0, 11))->map(function ($i) {
            $date = now()->subMonths(11 - $i);
            $data = $this->reportService->monthlyData(false, $date);
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
        $ref = $this->parseMonth($request->input('month'))->copy()->endOfMonth()->min(now());
        $days = collect(range(6, 0))->map(function ($i) use ($ref) {
            $date = $ref->copy()->subDays($i);
            $activities = Activity::whereDate('started_at', $date)->get();

            return [
                'date' => $date->format('Y-m-d'),
                'label' => $date->translatedFormat('D'),
                'total_minutes' => $activities->sum('duration_minutes'),
                'interruptions' => $activities->where('type', 'interruption')->count(),
                'development_minutes' => $activities->filter(fn($a) => $a->category?->type === 'development')->sum('duration_minutes'),
                'support_minutes' => $activities->filter(fn($a) => $a->category?->type === 'support')->sum('duration_minutes'),
            ];
        });

        return response()->json($days);
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
