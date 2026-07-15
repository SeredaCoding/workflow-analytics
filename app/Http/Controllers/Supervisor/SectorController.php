<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Sector;
use App\Services\ReportService;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    public function __construct(
        private ReportService $reportService,
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $date = $this->parseMonth($request->input('month'));

        if ($user->isAdmin()) {
            $sectors = Sector::with('users')->get();
        } else {
            $sectors = $user->supervisedSectors()->with('users')->get();
        }

        $allUsers = $sectors->flatMap->users->unique('id');
        $userIds = $allUsers->pluck('id');

        $totals = [
            'development_minutes' => 0,
            'support_minutes' => 0,
            'meeting_minutes' => 0,
            'avg_focus_sum' => 0,
            'avg_focus_count' => 0,
        ];

        $usersData = $allUsers->map(function ($user) use ($date, &$totals) {
            $monthly = $this->reportService->monthlyData($user->id, false, $date);

            $totals['development_minutes'] += $monthly['development_minutes'];
            $totals['support_minutes'] += $monthly['support_minutes'];
            $totals['meeting_minutes'] += $monthly['meeting_minutes'];
            if ($monthly['avg_focus_minutes'] > 0) {
                $totals['avg_focus_sum'] += $monthly['avg_focus_minutes'];
                $totals['avg_focus_count']++;
            }

            $inProgress = Activity::where('user_id', $user->id)
                ->inProgress()
                ->latest('started_at')
                ->with(['category', 'project'])
                ->first();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'sector' => $user->sector?->name,
                'total_minutes' => $monthly['total_minutes'],
                'total_hours' => $monthly['total_hours'],
                'dev_hours' => round($monthly['development_minutes'] / 60, 1),
                'sup_hours' => round($monthly['support_minutes'] / 60, 1),
                'mtg_hours' => round($monthly['meeting_minutes'] / 60, 1),
                'interruptions' => $monthly['interruptions'],
                'inProgress' => $inProgress ? [
                    'id' => $inProgress->id,
                    'title' => $inProgress->title,
                    'started_at' => $inProgress->started_at->toIso8601String(),
                ] : null,
            ];
        });

        $totalMinutes = $usersData->sum('total_minutes');
        $activeUsers = $usersData->filter(fn($u) => $u['total_minutes'] > 0)->count();

        return Inertia::render('Supervisor/SectorOverview', [
            'sectors' => $sectors->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
            'users' => $usersData,
            'month' => $date->format('Y-m'),
            'total_hours' => round($totalMinutes / 60, 1),
            'total_minutes' => $totalMinutes,
            'active_users' => $activeUsers,
            'total_users' => $allUsers->count(),
            'dev_hours' => round($totals['development_minutes'] / 60, 1),
            'support_hours' => round($totals['support_minutes'] / 60, 1),
            'meeting_hours' => round($totals['meeting_minutes'] / 60, 1),
            'avg_focus' => $totals['avg_focus_count'] > 0
                ? round($totals['avg_focus_sum'] / $totals['avg_focus_count'])
                : 0,
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
