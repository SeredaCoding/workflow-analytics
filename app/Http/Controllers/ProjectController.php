<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project = Project::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'color' => '#6366f1',
            'is_active' => true,
        ]);

        return response()->json([
            'id' => $project->id,
            'name' => $project->name,
        ]);
    }
}
