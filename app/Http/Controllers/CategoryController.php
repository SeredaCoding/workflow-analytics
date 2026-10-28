<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name']);

        if (Category::where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Já existe uma categoria com esse nome.'], 409);
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'color' => '#6366f1',
            'is_active' => true,
            'visibility' => 'user',
        ]);

        $category->users()->attach(auth()->id());

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'color' => $category->color,
        ]);
    }
}
