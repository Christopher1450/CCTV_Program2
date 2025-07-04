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
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'username'  => 'required|string|max:100|unique:users,username|regex:/^[a-zA-Z0-9_\-\.]+$/',
            'password'  => 'required|string|min:8|confirmed',
            'role_id'   => 'required|integer|exists:roles,id',
            'status'    => ['required', Rule::in([0, 1])],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Jangan expose password hash
        unset($user->password);

        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'sometimes|required|string|max:100',
            'username'  => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('users')->ignore($user->id),
                'regex:/^[a-zA-Z0-9_\-\.]+$/'
            ],
            'password'  => 'nullable|string|min:8|confirmed',
            'role_id'   => 'nulable|integer|exists:roles,id',
            'status'    => ['required', Rule::in([0, 1])],
        ]);

        // Jika password diisi, hash ulang
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Prevent editing superadmin if not allowed
        if ($user->role_id == 1 && Auth::user()->role_id != 1) {
            return response()->json(['message' => 'Unauthorized to update this user'], 403);
        }

        $user->update($validated);

        unset($user->password);

        return response()->json($user);
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
