<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    private function visibleProjectsQuery()
    {
        $user = auth()->user();

        return Project::where('is_active', true)
            ->where(function ($q) use ($user) {
                $q->where('visibility', 'global')
                  ->orWhere(function ($q) use ($user) {
                      $q->where('visibility', 'sector')
                        ->whereHas('sectors', fn($q) => $q->where('id', $user->sector_id));
                  })
                  ->orWhere(function ($q) use ($user) {
                      $q->where('visibility', 'user')
                        ->whereHas('users', fn($q) => $q->where('id', $user->id));
                  });
            });
    }

    public function index(Request $request)
    {
        $query = $this->visibleProjectsQuery()->withCount('activities');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $projects = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return inertia('Projects/Index', [
            'projects' => $projects,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name']);

        if (Project::where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Já existe um projeto com esse nome.'], 409);
        }

        $project = Project::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'slug' => $slug,
            'color' => '#6366f1',
            'is_active' => true,
            'visibility' => 'user',
        ]);

        $project->users()->attach(auth()->id());

        return response()->json([
            'id' => $project->id,
            'name' => $project->name,
        ]);
    }

    public function show(Request $request, Project $project)
    {
        $user = auth()->user();

        $visible = Project::where('id', $project->id)->where('is_active', true)
            ->where(function ($q) use ($user) {
                $q->where('visibility', 'global')
                  ->orWhere(function ($q) use ($user) {
                      $q->where('visibility', 'sector')
                        ->whereHas('sectors', fn($q) => $q->where('id', $user->sector_id));
                  })
                  ->orWhere(function ($q) use ($user) {
                      $q->where('visibility', 'user')
                        ->whereHas('users', fn($q) => $q->where('id', $user->id));
                  });
            })->exists();

        if (!$visible) {
            abort(404);
        }

        $project->load(['sectors', 'users', 'links']);

        $activitiesQuery = Activity::where('project_id', $project->id)
            ->with(['user', 'category'])
            ->orderBy('started_at', 'desc');

        $activities = (clone $activitiesQuery)->paginate(50)->withQueryString();

        $allActivities = Activity::where('project_id', $project->id)->get();

        $totalMinutes = $allActivities->sum('duration_minutes');
        $totalActivities = $allActivities->count();
        $uniqueUsers = $allActivities->whereNotNull('user_id')->unique('user_id')->count();

        $dateRange = [];
        if ($allActivities->isNotEmpty()) {
            $min = $allActivities->min('started_at');
            $max = $allActivities->max('started_at');
            if ($min && $max) {
                $dateRange = [
                    'start' => $min->format('Y-m-d'),
                    'end' => $max->format('Y-m-d'),
                    'days' => (int) $min->diffInDays($max) + 1,
                ];
            }
        }

        $categoryDistribution = $allActivities
            ->where('type', 'activity')
            ->groupBy(fn($a) => $a->category?->name ?? 'Sem categoria')
            ->map(function ($items) {
                $first = $items->first();
                $mins = $items->sum('duration_minutes');
                return [
                    'name' => $first->category?->name ?? 'Sem categoria',
                    'color' => $first->category?->color ?? '#6366f1',
                    'minutes' => $mins,
                ];
            })->values();

        $totalForPercent = max($categoryDistribution->sum('minutes'), 1);
        $categoryDistribution = $categoryDistribution->map(fn($c) => [
            'name' => $c['name'],
            'color' => $c['color'],
            'minutes' => $c['minutes'],
            'percentage' => round(($c['minutes'] / $totalForPercent) * 100),
        ]);

        $topUsers = $allActivities
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->map(function ($items) {
                $first = $items->first();
                $mins = $items->sum('duration_minutes');
                return [
                    'name' => $first->user?->name ?? 'Desconhecido',
                    'minutes' => $mins,
                    'count' => $items->count(),
                ];
            })->sortByDesc('minutes')->values();

        $totalUserPercent = max($topUsers->sum('minutes'), 1);
        $topUsers = $topUsers->map(fn($u) => [
            'name' => $u['name'],
            'minutes' => $u['minutes'],
            'count' => $u['count'],
            'percentage' => round(($u['minutes'] / $totalUserPercent) * 100),
        ]);

        $monthlyHistory = collect(range(11, 0))->map(function ($i) use ($project) {
            $date = now()->subMonths($i);
            $mins = Activity::where('project_id', $project->id)
                ->whereYear('started_at', $date->year)
                ->whereMonth('started_at', $date->month)
                ->sum('duration_minutes');
            return [
                'month' => $date->translatedFormat('M/Y'),
                'minutes' => (int) $mins,
            ];
        });

        return inertia('Projects/Show', [
            'project' => $project,
            'activities' => $activities,
            'stats' => [
                'total_minutes' => (int) $totalMinutes,
                'total_hours' => round($totalMinutes / 60, 1),
                'total_activities' => $totalActivities,
                'unique_users' => $uniqueUsers,
                'period_days' => $dateRange['days'] ?? 0,
                'period_start' => $dateRange['start'] ?? null,
                'period_end' => $dateRange['end'] ?? null,
            ],
            'categoryDistribution' => $categoryDistribution,
            'topUsers' => $topUsers,
            'monthlyHistory' => $monthlyHistory,
            'filters' => $request->only(['search']),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $user = auth()->user();

        $canEdit = $user->isAdmin()
            || $project->users()->where('user_id', $user->id)->exists()
            || ($project->visibility === 'sector' && $user->sector_id && $project->sectors()->where('id', $user->sector_id)->exists());

        if (!$canEdit) {
            abort(403, 'Você não pode editar este projeto.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'color' => 'nullable|string|max:7',
        ]);

        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#6366f1',
        ]);

        return redirect()->back()->with('success', 'Projeto atualizado com sucesso!');
    }
}
