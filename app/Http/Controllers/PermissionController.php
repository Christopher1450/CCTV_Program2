<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $permissions = Permission::paginate($limit);

        return response()->json([
            'data' => $permissions->items(),
            'total' => $permissions->total(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'permission_name' => 'required|string|unique:permissions,permission_name|max:255',
        ]);

        $validated['created_by'] = $request->input('created_by');

        $permission = Permission::create($validated);

        return response()->json($permission, 201);
    }

    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json($permission);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'permission_name' => 'string|max:255|unique:permissions,permission_name,' . $id,
        ]);

        $validated['updated_by'] = $request->input('updated_by');

        $permission->update($validated);

        return response()->json($permission);
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully']);
    }
}
