<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Activity;
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
        $supervisor = auth()->user();
        $date = $this->parseMonth($request->input('month'));
        $sectors = $supervisor->supervisedSectors()->with('users')->get();

        $users = $sectors->flatMap->users;
        $userIds = $users->pluck('id');

        $usersData = $users->map(function ($user) use ($date) {
            $monthly = $this->reportService->monthlyData($user->id, false, $date);

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
            'total_users' => $users->count(),
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
