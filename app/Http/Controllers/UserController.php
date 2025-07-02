<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $users = User::with('role')->paginate($limit);

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
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'username'  => 'required|string|max:100|unique:users,username',
            'password'  => 'required|string|max:200|min:6',
            'role_id'   => 'unsignedBigInteger|exists:roles,id',
            'status'    => 'unsignedTinyInteger|in:0,1', // 0 for inactive, 1 for active
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'string|max:100',
            'username'  => 'string|max:100|unique:users,username,' . $id,
            'password'  => 'string|max:200|min:6',
            'role_id'   => 'unsignedBigInteger|exists:roles,id',
            'status'    => 'unsignedTinyInteger|in:0,1', // 0 for inactive, 1 for active
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted.']);
    }
}
