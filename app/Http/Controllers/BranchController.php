<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BranchController extends Controller
{
    public function index()
    {
        return response()->json(Branch::with(['provider', 'ipCamAccount'])->get());
    }

    public function store(Request $request)
    {
    // $hrisData = Http::withToken('token')->get("https://api/branches/{$request->hris_id}")->json();

    $branch = Branch::create([
    //     'name'          => $hrisData['name'],
    //     'phone'         => $hrisData['phone'],
    //     'address'       => $hrisData['address'],
    //     'latitude'      => $hrisData['latitude'],
    //     'longitude'     => $hrisData['longitude'],
    //     'area_manager'  => $hrisData['area_manager'],
    //     'branch_head'   => $hrisData['branch_head'],
        // sisanya dari input user:
        'internet_provider_id'   => $request->internet_provider_id,
        'internet_customer_id'   => $request->internet_customer_id,
        'cctv_type'              => $request->cctv_type,
        'ip_cam_account_id'      => $request->ip_cam_account_id,
    ]);

    return response()->json($branch);
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
            'internet_provider_id'  => 'nullable|exists:internet_providers,id',
            'internet_customer_id'  => 'nullable|string|max:60',
            'cctv_type'             => 'nullable|in:1,2', // 1: IP Camera, 2: Analog Camera
            'ip_cam_account_id'     => 'nullable|exists:ip_cam_accounts,id',
        ]);

        $branch->update($validated);

            // $branch->update($request->only([
            //     'internet_provider_id',
            //     'internet_customer_id',
            //     'cctv_type',
            //     'ip_cam_account_id',
            // ]));

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
    public function searchByName(Request $request)
    {
        $name = $request->query('name');
        $branch = \App\Models\Branch::where('name', $name)->firstOrFail();
        return response()->json(['id' => $branch->id]);
    }
    public function syncFromHRIS($id)
    {
        $response = Http::withToken('token_hris')->get("https://external-hris.com/api/branches/{$id}");
        if ($response->failed()) {
            return response()->json(['message' => 'Cabang tidak ditemukan'], 404);
        }

        return response()->json($response->json());
    }
}
