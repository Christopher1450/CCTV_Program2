<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        return response()->json(Branch::with(['provider', 'ipCamAccount'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'internet_provider_id' => 'required|exists:internet_providers,id',
            // 'internet_customer_id' => 'required|string|max:60',
            'cctv_type' => 'required|in:1,2',
            'ip_cam_account_id' => 'required|exists:ip_cam_accounts,id',
        ]);

        $branch = Branch::create($validated);

        return response()->json($branch, 201);
    }

    public function show($id)
    {
        $branch = Branch::with(['provider', 'ipCamAccount'])->findOrFail($id);
        return response()->json($branch);
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'internet_provider_id' => 'required|exists:internet_providers,id',
            // 'internet_customer_id' => 'required|string|max:60',
            'cctv_type' => 'required|in:1,2',
            'ip_cam_account_id' => 'required|exists:ip_cam_accounts,id',
        ]);

        $branch->update($request->only([
            'internet_provider_id',
            'internet_customer_id',
            'cctv_type',
            'ip_cam_account_id',
        ]));

        return response()->json([
            'message' => 'Branch updated successfully',
            'branch' => $branch
        ]);
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return response()->json(['message' => 'Branch deleted successfully']);
    }
}
