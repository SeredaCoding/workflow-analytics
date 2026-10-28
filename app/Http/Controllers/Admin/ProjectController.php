<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Activity;
use App\Models\Sector;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::select('projects.*')->with(['sectors', 'users.sector'])->withCount('activities')->distinct();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($visibility = $request->input('visibility')) {
            $query->where('visibility', $visibility);
        }

        if ($userType = $request->input('user_type')) {
            $query->where('visibility', 'user');
            $userId = $request->user()->id;

            if ($userType === 'personal') {
                $query->whereHas('users', fn($q) => $q->where('user_id', $userId))
                    ->whereRaw('(SELECT COUNT(*) FROM project_user WHERE project_user.project_id = projects.id) = 1');
            } elseif ($userType === 'individual') {
                $query->whereDoesntHave('users', fn($q) => $q->where('user_id', $userId))
                    ->whereRaw('(SELECT COUNT(*) FROM project_user WHERE project_user.project_id = projects.id) = 1');
            } elseif ($userType === 'shared') {
                $query->whereRaw('(SELECT COUNT(*) FROM project_user WHERE project_user.project_id = projects.id) > 1');
            } elseif ($userType === 'multisector') {
                $query->whereRaw('(SELECT COUNT(*) FROM project_user WHERE project_user.project_id = projects.id) > 1')
                    ->whereRaw('(SELECT COUNT(DISTINCT u.sector_id) FROM project_user pu INNER JOIN users u ON u.id = pu.user_id WHERE pu.project_id = projects.id) > 1');
            }
        }

        $projects = $query->orderBy('projects.name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Projects', [
            'projects' => $projects,
            'sectors' => Sector::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'filters' => $request->only(['search', 'visibility', 'user_type']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
            'sector_ids' => 'required|array|min:1',
            'sector_ids.*' => 'exists:sectors,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $visibility = !empty($validated['user_ids']) ? 'user' : 'sector';

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'slug' => $slug,
            'color' => $validated['color'] ?? randomHexColor(),
            'is_active' => $validated['is_active'] ?? true,
            'visibility' => $visibility,
        ]);

        $project->sectors()->sync($validated['sector_ids']);

        if (!empty($validated['user_ids'])) {
            $project->users()->sync($validated['user_ids']);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Projeto criado com sucesso!');
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
            'sector_ids' => 'required|array|min:1',
            'sector_ids.*' => 'exists:sectors,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 1;
        while (Project::where('slug', $slug)->where('id', '!=', $project->id)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $visibility = !empty($validated['user_ids']) ? 'user' : 'sector';

        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'slug' => $slug,
            'color' => $validated['color'] ?? randomHexColor(),
            'is_active' => $validated['is_active'] ?? true,
            'visibility' => $visibility,
        ]);

        $project->sectors()->sync($validated['sector_ids']);
        $project->users()->sync($validated['user_ids'] ?? []);

        return redirect()->route('admin.projects.index')->with('success', 'Projeto atualizado com sucesso!');
    }

    public function destroy(Project $project)
    {
        Activity::where('project_id', $project->id)->update(['project_id' => null]);
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Projeto excluído com sucesso!');
    }
}
