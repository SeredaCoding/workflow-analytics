<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Sector;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['sector', 'role'])->orderBy('name');

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }

        $users = $query->get();
        $sectors = Sector::orderBy('name')->get();
        $roles = Role::orderBy('id')->get();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'sectors' => $sectors,
            'roles' => $roles,
            'filters' => $request->only(['role_id', 'sector_id']),
        ]);
    }

    public function edit(User $user)
    {
        $user->load(['sector', 'role']);
        $sectors = Sector::orderBy('name')->get();
        $roles = Role::orderBy('id')->get();

        return Inertia::render('Admin/UserEdit', [
            'user' => $user,
            'sectors' => $sectors,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'sector_id' => 'nullable|exists:sectors,id',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Usuário atualizado com sucesso!');
    }
}
