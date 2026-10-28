<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;

class UserReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
    ) {}

    public function show(Request $request, User $user)
    {
        $supervisor = auth()->user();
        $supervisedSectorIds = $supervisor->supervisedSectors()->pluck('id');

        if (!$supervisor->isAdmin() && !$supervisedSectorIds->contains($user->sector_id)) {
            abort(403, 'Você não tem permissão para visualizar este usuário.');
        }

        $date = $this->parseMonth($request->input('month'));

        $monthly = $this->reportService->monthlyData($user->id, false, $date);
        $categoryDistribution = $this->reportService->categoryDistribution($user->id, false, $date);
        $projectDistribution = $this->reportService->projectDistribution($user->id, false, $date);
        $contextDistribution = $this->reportService->contextDistribution($user->id, false, $date);
        $dailyBreakdown = $this->reportService->dailyBreakdown($user->id, false, $date);
        $topActivities = $this->reportService->topActivities($user->id, false, $date);

        $totalMinutes = $monthly['total_minutes'] ?: 1;

        $userInProgress = Activity::where('user_id', $user->id)
            ->inProgress()
            ->latest('started_at')
            ->with(['category', 'project', 'context'])
            ->first();

        $activities = Activity::where('user_id', $user->id)
            ->whereYear('started_at', $date->year)
            ->whereMonth('started_at', $date->month)
            ->with(['category', 'project'])
            ->orderBy('started_at', 'desc')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'type' => $a->type,
                'category' => $a->category?->name,
                'category_color' => $a->category?->color,
                'project' => $a->project?->name,
                'started_at' => $a->started_at->toIso8601String(),
                'ended_at' => $a->ended_at?->toIso8601String(),
                'duration' => $a->duration_minutes,
                'status' => $a->status,
            ]);

        return Inertia::render('Supervisor/UserDetail', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'sector' => $user->sector?->name,
            ],
            'month' => $date->format('Y-m'),
            'monthly' => $monthly,
            'categoryDistribution' => $categoryDistribution,
            'projectDistribution' => $projectDistribution,
            'contextDistribution' => $contextDistribution,
            'dailyBreakdownHtml' => $this->reportService->buildDailyHtml($dailyBreakdown, true),
            'weeklySummaryHtml' => $this->reportService->buildWeeklyHtml($dailyBreakdown, true, $date),
            'topActivitiesHtml' => $this->reportService->buildTopActivitiesHtml($topActivities, $totalMinutes, true),
            'userInProgress' => $userInProgress ? [
                'id' => $userInProgress->id,
                'title' => $userInProgress->title,
                'type' => $userInProgress->type,
                'category' => $userInProgress->category?->name,
                'project' => $userInProgress->project?->name,
                'context' => $userInProgress->context?->name,
                'started_at' => $userInProgress->started_at->toIso8601String(),
            ] : null,
            'activities' => $activities,
        ]);
    }

    private function parseMonth(?string $month): Carbon
    {
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            return Carbon::parse($month . '-01');
        }
        return now();
    }
}
