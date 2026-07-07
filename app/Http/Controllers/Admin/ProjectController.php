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
        $query = Project::select('projects.*')->with(['sectors', 'users'])->withCount('activities')->distinct();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $projects = $query->orderBy('projects.name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Projects', [
            'projects' => $projects,
            'sectors' => Sector::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
            'visibility' => 'required|string|in:global,sector,user',
            'sector_ids' => 'nullable|array',
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

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'slug' => $slug,
            'color' => $validated['color'] ?? '#6366f1',
            'is_active' => $validated['is_active'] ?? true,
            'visibility' => $validated['visibility'],
        ]);

        if ($validated['visibility'] === 'sector' && !empty($validated['sector_ids'])) {
            $project->sectors()->sync($validated['sector_ids']);
        }

        if ($validated['visibility'] === 'user' && !empty($validated['user_ids'])) {
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
            'visibility' => 'required|string|in:global,sector,user',
            'sector_ids' => 'nullable|array',
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

        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'slug' => $slug,
            'color' => $validated['color'] ?? '#6366f1',
            'is_active' => $validated['is_active'] ?? true,
            'visibility' => $validated['visibility'],
        ]);

        if ($validated['visibility'] === 'sector') {
            $project->sectors()->sync($validated['sector_ids'] ?? []);
            $project->users()->sync([]);
        } elseif ($validated['visibility'] === 'user') {
            $project->sectors()->sync([]);
            $project->users()->sync($validated['user_ids'] ?? []);
        } else {
            $project->sectors()->sync([]);
            $project->users()->sync([]);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Projeto atualizado com sucesso!');
    }

    public function destroy(Project $project)
    {
        Activity::where('project_id', $project->id)->update(['project_id' => null]);
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Projeto excluído com sucesso!');
    }
}
