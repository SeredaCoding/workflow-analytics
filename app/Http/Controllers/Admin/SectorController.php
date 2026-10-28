<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    public function index()
    {
        $sectors = Sector::with('supervisor')->withCount('users')->orderBy('name')->get();

        $supervisors = User::whereIn('role_id', [2, 3])->orderBy('name')->get();

        return Inertia::render('Admin/Sectors', [
            'sectors' => $sectors,
            'supervisors' => $supervisors,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);

        Sector::create($validated);

        return redirect()->route('admin.sectors.index')->with('success', 'Setor criado com sucesso!');
    }

    public function edit(Sector $sector)
    {
        $sector->load('supervisor');
        $supervisors = User::whereIn('role_id', [2, 3])->orderBy('name')->get();

        return Inertia::render('Admin/SectorEdit', [
            'sector' => $sector,
            'supervisors' => $supervisors,
        ]);
    }

    public function update(Request $request, Sector $sector)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);

        $sector->update($validated);

        return redirect()->route('admin.sectors.index')->with('success', 'Setor atualizado com sucesso!');
    }

    public function destroy(Sector $sector)
    {
        $sector->users()->update(['sector_id' => null]);
        $sector->delete();

        return redirect()->route('admin.sectors.index')->with('success', 'Setor excluído com sucesso!');
    }
}
