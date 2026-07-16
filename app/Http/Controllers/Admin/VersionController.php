<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Version;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VersionController extends Controller
{
    public function index()
    {
        $versions = Version::orderBy('created_at', 'desc')->paginate(20);

        return Inertia::render('Admin/Versions', [
            'versions' => $versions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'version' => 'required|string|max:20',
            'release_date' => 'nullable|date',
            'description' => 'nullable|string|max:10000',
            'changes' => 'nullable|array',
            'changes.*' => 'string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['changes'] = $validated['changes'] ?? [];

        Version::create($validated);

        return redirect()->route('admin.versions.index')->with('success', 'Versão criada com sucesso!');
    }

    public function update(Request $request, Version $version)
    {
        $validated = $request->validate([
            'version' => 'required|string|max:20',
            'release_date' => 'nullable|date',
            'description' => 'nullable|string|max:10000',
            'changes' => 'nullable|array',
            'changes.*' => 'string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['changes'] = $validated['changes'] ?? [];

        $version->update($validated);

        return redirect()->route('admin.versions.index')->with('success', 'Versão atualizada com sucesso!');
    }

    public function destroy(Version $version)
    {
        $version->delete();

        return redirect()->route('admin.versions.index')->with('success', 'Versão excluída!');
    }
}
