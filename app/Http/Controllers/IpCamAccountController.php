<?php

namespace App\Http\Controllers;

use App\Models\IpCamAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IpCamAccountController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $query = IpCamAccount::query();

        if ($request->has('q') && !empty($request->q)) {
            $keyword = strtolower($request->q);
            $query->whereRaw('LOWER(email) LIKE ?', ["%{$keyword}%"]);
        }

        $accounts = $query->orderBy('id', 'asc')->paginate($limit);

        return response()->json([
            'data' => $accounts->items(),
            'total' => $accounts->total(),
            'current_page' => $accounts->currentPage(),
            'last_page' => $accounts->lastPage(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate only email (tidak perlu created_by disini)
        $validated = $request->validate([
            'email' => 'required|email|unique:ip_cam_accounts,email',
        ]);

        // Tambahkan created_by manual
        $validated['created_by'] = $user->id;

        $account = IpCamAccount::create($validated);

        return response()->json($account, 201);
    }


    public function show($id)
    {
        $account = IpCamAccount::findOrFail($id);
        return response()->json($account);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user(); // user login
        $account = IpCamAccount::findOrFail($id);

        $validated = $request->validate([
            'email' => 'required|email|unique:ip_cam_accounts,email,' . $id,
        ]);

        // Tambahkan updated_by otomatis
        $validated['updated_by'] = $user->id;

        $account->update($validated);

        return response()->json([
            'message' => 'Account updated successfully',
            'data' => $account
        ]);
    }


    public function destroy($id)
    {
        $account = IpCamAccount::findOrFail($id);
        $account->delete();

        return response()->json(['message' => 'IP Cam Account deleted successfully']);
    }
}
