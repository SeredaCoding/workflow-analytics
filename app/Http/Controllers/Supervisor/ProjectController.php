<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Sector;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    private function getSectorIds(): array
    {
        $supervisor = auth()->user();
        $ids = $supervisor->supervisedSectors()->pluck('id')->toArray();
        if ($supervisor->sector_id && !in_array($supervisor->sector_id, $ids)) {
            $ids[] = $supervisor->sector_id;
        }
        return $ids;
    }

    public function index(Request $request)
    {
        $supervisor = auth()->user();

        if ($supervisor->isAdmin()) {
            return redirect()->route('admin.projects.index');
        }

        $sectorIds = $this->getSectorIds();

        $query = Project::where('is_active', true)->visibleTo($supervisor)->with(['sectors', 'users'])->withCount('activities');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($visibility = $request->input('visibility')) {
            $query->where('visibility', $visibility);
        }

        $projects = $query->orderBy('name')->paginate(15)->withQueryString();

        $sectorUsers = User::whereIn('sector_id', $sectorIds)->orderBy('name')->get();
        $sectors = Sector::whereIn('id', $sectorIds)->orderBy('name')->get();

        return Inertia::render('Supervisor/Projects', [
            'projects' => $projects,
            'sectorUsers' => $sectorUsers,
            'sectors' => $sectors,
            'filters' => $request->only(['search', 'visibility']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
            'sector_ids' => 'required|array',
            'sector_ids.*' => 'exists:sectors,id',
        ]);

        $allowedSectorIds = $this->getSectorIds();

        $sectorIds = array_intersect($validated['sector_ids'], $allowedSectorIds);
        if (empty($sectorIds)) {
            return redirect()->back()->with('error', 'Você só pode associar projetos aos setores que supervisiona.');
        }

        $slug = \Illuminate\Support\Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $project = Project::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'color' => $validated['color'] ?? randomHexColor(),
            'is_active' => $validated['is_active'] ?? true,
            'visibility' => 'sector',
        ]);

        $project->sectors()->sync($sectorIds);

        return redirect()->route('supervisor.projects.index')->with('success', 'Projeto criado com sucesso!');
    }

    public function update(Request $request, Project $project)
    {
        $allowedSectorIds = $this->getSectorIds();

        if (!$project->sectors()->whereIn('sector_id', $allowedSectorIds)->exists()) {
            abort(403, 'Você não pode editar este projeto.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
            'sector_ids' => 'required|array',
            'sector_ids.*' => 'exists:sectors,id',
        ]);

        $sectorIds = array_intersect($validated['sector_ids'], $allowedSectorIds);
        if (empty($sectorIds)) {
            return redirect()->back()->with('error', 'Você só pode associar projetos aos setores que supervisiona.');
        }

        $slug = \Illuminate\Support\Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 1;
        while (Project::where('slug', $slug)->where('id', '!=', $project->id)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $project->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'color' => $validated['color'] ?? randomHexColor(),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $project->sectors()->sync($sectorIds);

        return redirect()->route('supervisor.projects.index')->with('success', 'Projeto atualizado com sucesso!');
    }

    public function destroy(Project $project)
    {
        if (auth()->user()->role_id !== 3) {
            $allowedSectorIds = $this->getSectorIds();

            if (!$project->sectors()->whereIn('sector_id', $allowedSectorIds)->exists()) {
                abort(403, 'Você não pode excluir este projeto.');
            }
        }

        Activity::where('project_id', $project->id)->update(['project_id' => null]);
        $project->delete();

        return redirect()->route('supervisor.projects.index')->with('success', 'Projeto excluído com sucesso!');
    }

    public function show(Request $request, Project $project)
    {
        $supervisor = auth()->user();
        $sectorIds = $this->getSectorIds();

        $visible = Project::where('id', $project->id)->where('is_active', true)
            ->visibleTo($supervisor)->exists();

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

    public function users(Project $project)
    {
        $sectorIds = $this->getSectorIds();

        if (!$project->sectors()->whereIn('sector_id', $sectorIds)->exists()) {
            abort(403);
        }

        $project->load('users');
        $sectorUsers = User::whereIn('sector_id', $sectorIds)->orderBy('name')->get();

        return response()->json([
            'project' => $project,
            'sectorUsers' => $sectorUsers,
        ]);
    }

    public function assignUser(Request $request, Project $project)
    {
        $sectorIds = $this->getSectorIds();

        if (!$project->sectors()->whereIn('sector_id', $sectorIds)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        if (!in_array($user->sector_id, $sectorIds)) {
            return response()->json(['message' => 'Usuário não pertence ao seu setor.'], 422);
        }

        $project->users()->syncWithoutDetaching([$validated['user_id']]);

        return response()->json(['message' => 'Usuário associado com sucesso!']);
    }

    public function removeUser(Project $project, User $user)
    {
        $sectorIds = $this->getSectorIds();

        if (!$project->sectors()->whereIn('sector_id', $sectorIds)->exists()) {
            abort(403);
        }

        $project->users()->detach($user->id);

        return response()->json(['message' => 'Usuário removido com sucesso!']);
    }
}
