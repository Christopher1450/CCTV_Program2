<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $limit = intval($request->input('limit', 10));
        $limit = $limit > 50 ? 50 : $limit; // Batasi max 50 per page

        $query = User::with('role');

        // Cek jika ada query search (misalnya ?q=nama)
        if ($request->has('q') && !empty($request->q)) {
            $search = strtolower($request->q);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(username) LIKE ?', ["%{$search}%"]);
                // ->orWhereRaw('LOWER(role) LIKE ?', bindings: ["%{$search}%"]);
            });
        }

        $users = $query->paginate($limit);

        return response()->json([
            'data'          => $users->items(),
            'total'         => $users->total(),
            'current_page'  => $users->currentPage(),
            'last_page'     => $users->lastPage(),
        ]);
    }

    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);

        // Jangan kirim password hash ke frontend
        unset($user->password);

        return response()->json($user);
    }

    public function store(Request $request)
    {
        $user = Auth::user(); // the user who is creating

        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'username'  => 'required|string|max:100|unique:users,username|regex:/^[a-zA-Z0-9_\-\.]+$/',
            'password'  => 'required|string|min:8|confirmed',
            'role_id'   => 'required|integer|exists:roles,id',
            'status'    => ['required', Rule::in([0, 1])],
        ]);

        // Assign created_by and updated_by
        $validated['created_by'] = $user->id;

        $validated['password'] = Hash::make($validated['password']);

        $newUser = User::create($validated);

        // Load created_by relationship
        $newUser->load(['createdBy:id,name', 'updatedBy:id,name']);

        unset($newUser->password); // don’t return password hash

        return response()->json([
            'id'          => $newUser->id,
            'name'        => $newUser->name,
            'username'    => $newUser->username,
            'role_id'     => $newUser->role_id,
            'status'      => $newUser->status,
            'created_at'  => $newUser->created_at,
            'updated_at'  => $newUser->updated_at,
            'created_by'  => $newUser->createdBy,
            'updated_by'  => $newUser->updatedBy,
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $existingUser = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'sometimes|required|string|max:100',
            'username'  => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('users')->ignore($existingUser->id),
                'regex:/^[a-zA-Z0-9_\-\.]+$/'
            ],
            'password'  => 'nullable|string|min:8|confirmed',
            'role_id'   => 'nullable|integer|exists:roles,id',
            'status'    => ['required', Rule::in([0, 1])],
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['updated_by'] = $user->id;

        // Protect superadmin
        if ($existingUser->role_id == 1 && $user->role_id != 1) {
            return response()->json(['message' => 'Unauthorized to update this user'], 403);
        }

        $existingUser->update($validated);

        $existingUser->load(['createdBy:id,name', 'updatedBy:id,name']);
        unset($existingUser->password);

        return response()->json([
            'id'          => $existingUser->id,
            'name'        => $existingUser->name,
            'username'    => $existingUser->username,
            'role_id'     => $existingUser->role_id,
            'status'      => $existingUser->status,
            'created_at'  => $existingUser->created_at,
            'updated_at'  => $existingUser->updated_at,
            'created_by'  => $existingUser->createdBy,
            'updated_by'  => $existingUser->updatedBy,
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself or superadmin
        if (auth::user() == $user->id || $user->role_id == 1) {
            return response()->json(['message' => 'You cannot delete this user'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
