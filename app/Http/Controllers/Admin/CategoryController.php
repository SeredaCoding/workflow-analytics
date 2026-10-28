<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Activity;
use App\Models\Sector;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['sectors', 'users'])->withCount('activities')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Categories', [
            'categories' => $categories,
            'sectors' => Sector::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
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
        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'color' => $validated['color'] ?? '#6366f1',
            'is_active' => $validated['is_active'] ?? true,
            'visibility' => $validated['visibility'],
        ]);

        if ($validated['visibility'] === 'sector' && !empty($validated['sector_ids'])) {
            $category->sectors()->sync($validated['sector_ids']);
        }

        if ($validated['visibility'] === 'user' && !empty($validated['user_ids'])) {
            $category->users()->sync($validated['user_ids']);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Categoria criada com sucesso!');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
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
        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'color' => $validated['color'] ?? '#6366f1',
            'is_active' => $validated['is_active'] ?? true,
            'visibility' => $validated['visibility'],
        ]);

        if ($validated['visibility'] === 'sector') {
            $category->sectors()->sync($validated['sector_ids'] ?? []);
            $category->users()->sync([]);
        } elseif ($validated['visibility'] === 'user') {
            $category->sectors()->sync([]);
            $category->users()->sync($validated['user_ids'] ?? []);
        } else {
            $category->sectors()->sync([]);
            $category->users()->sync([]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Category $category)
    {
        Activity::where('category_id', $category->id)->update(['category_id' => null]);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Categoria excluída com sucesso!');
    }
}
