<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
        {
            return Role::with('permissions')->get();
        }

        public function store(Request $request)
        {
            $validated = $request->validate([
                'role_name' => 'required|string|unique:roles',
                'permissions' => 'array', // [1,2,3]
            ]);

            $role = Role::create([
                'role_name' => $validated['role_name'],
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
            $validated = $request->validate([
                'role_name' => 'required|string|unique:roles,role_name,' . $role->id,
                'permissions' => 'array',
            ]);

            $role->update(['role_name' => $validated['role_name']]);
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
