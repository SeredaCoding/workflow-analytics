<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityContext;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityContext::orderBy('sort_order')->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $modules = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Modules', [
            'modules' => $modules,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 1;
        while (ActivityContext::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        ActivityContext::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'color' => $validated['color'] ?? '#6366f1',
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.modules.index')->with('success', 'Módulo criado com sucesso!');
    }

    public function update(Request $request, ActivityContext $module)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 1;
        while (ActivityContext::where('slug', $slug)->where('id', '!=', $module->id)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $module->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'color' => $validated['color'] ?? '#6366f1',
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.modules.index')->with('success', 'Módulo atualizado com sucesso!');
    }

    public function destroy(ActivityContext $module)
    {
        $module->delete();

        return redirect()->route('admin.modules.index')->with('success', 'Módulo excluído com sucesso!');
    }
}
