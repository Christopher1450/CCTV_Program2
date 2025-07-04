<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;


class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::with('permissions');

        // Ambil query param limit, default 10
        $limit = $request->input('limit', 10);

        return $query->paginate($limit);
    }


    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'role_name'     => 'required|string|unique:roles',
            'permissions'   => 'array', // [1,2,3]
        ]);

        $validated['created_by'] = $user->id;

        $role = Role::create([
            'role_name'   => $validated['role_name'],
            'created_by'  => $validated['created_by'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return response()->json($role->load('permissions'), 201);
    }

    public function show(Role $role)
    {
        return $role->load('permissions');
    }

    public function update(Request $request, Role $role)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'role_name'     => 'nullable|string|unique:roles,role_name,' . $role->id,
            'permissions'   => 'array',
        ]);

        // Tambahkan updated_by otomatis
        $validated['updated_by'] = $user->id;

        $role->update([
            'role_name'   => $validated['role_name'] ?? $role->role_name,
            'updated_by'  => $validated['updated_by'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return response()->json($role->load('permissions'));
    }


    public function destroy(Role $role)
    {
        $role->permissions()->detach();
        $role->delete();
        return response()->noContent();
    }
}
