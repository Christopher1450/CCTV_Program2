<?php

namespace App\Http\Controllers;

use App\Models\IpCamAccount;
use Illuminate\Http\Request;

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
        $request->validate([
            'email' => 'required|email|unique:ip_cam_accounts,email',
        ]);

        $account = IpCamAccount::create($request->only('email'));

        return response()->json($account, 201);
    }

    public function show($id)
    {
        $account = IpCamAccount::findOrFail($id);
        return response()->json($account);
    }

    public function update(Request $request, $id)
    {
        $account = IpCamAccount::findOrFail($id);

        $request->validate([
            'email' => 'required|email|unique:ip_cam_accounts,email,' . $id,
        ]);

        $account->update($request->only('email'));

        return response()->json($account);
    }

    public function destroy($id)
    {
        $account = IpCamAccount::findOrFail($id);
        $account->delete();

        return response()->json(['message' => 'IP Cam Account deleted successfully']);
    }
}
