<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use App\Models\CctvPosition;
use App\Models\Branch;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CctvController extends Controller
{
    public function index(Request $request)
    {
        $query = Cctv::with(['branch.ipCamAccount', 'position']);

        // Optional: filter pencarian
        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        // Filter berdasarkan koneksi
        if ($request->has('connectionStatus')) {
            $status = $request->connectionStatus === 'Connected' ? 1 : 0;
            $query->where('connection_status', $status);
        }

        // Filter berdasarkan nama cabang
        if ($request->filled('branch')) {
            $branchName = $request->branch;
            $query->whereHas('branch', function ($q) use ($branchName) {
                $q->where('name', 'like', '%' . $branchName . '%');
            });
        }

        $limit = $request->get('limit', 10);
        return response()->json($query->paginate($limit));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'             => 'required|exists:branches,id',
            'cctv_position_id'      => 'required|exists:cctv_positions,id',
            'name'                  => 'required|string|unique:cctvs,name,NULL,id,branch_id,' . $request->branch_id,
            'is_active'             => 'boolean',
            'connection_status'     => 'in:1,2',
            'playback_status'       => 'in:1,2',
            // 'replacement_status'    => 'in:1,2',
        ]);

        $validated['created_by'] = Auth::user();

        $cctv = Cctv::create($validated);

        return response()->json($cctv, 201);
    }

    public function show($id)
    {
        $cctv = Cctv::with(['branch', 'position', 'ipCamAccount'])->findOrFail($id);
        return response()->json($cctv);
    }

    public function update(Request $request, $id)
    {
        $cctv = Cctv::findOrFail($id);

        $validated = $request->validate([
            'branch_id'             => 'required|exists:branches,id',
            'cctv_position_id'      => 'required|exists:cctv_positions,id',
            'name'                  => 'required|string|unique:cctvs,name,' . $id . ',id,branch_id,' . $request->branch_id,
            'is_active'             => 'boolean',
            'connection_status'     => 'in:1,2',
            'playback_status'       => 'in:1,2',
            // 'replacement_status'    => 'in:1,2',
        ]);

        $validated['updated_by'] = Auth::id();

        $cctv->update($validated);

        return response()->json($cctv);
    }

    public function destroy($id)
    {
        $cctv = Cctv::findOrFail($id);
        $cctv->delete();

        return response()->json(['message' => 'CCTV deleted successfully']);
    }

    public function searchByName(Request $request)
    {
        $name = $request->query('name');
        $cctv = Cctv::where('name', $name)->firstOrFail();
        return response()->json(['id' => $cctv->id]);
    }

    public function getByBranch($branch_id)
{
    $cctvs = Cctv::with(['branch', 'position', 'ipCamAccount'])
        ->where('branch_id', $branch_id)
        ->get();

    foreach ($cctvs as $cctv) {
        $wo = WorkOrder::where('cctv_id', $cctv->id)
            ->whereIn('status', [1, 2])
            ->latest()
            ->first();

        if ($wo) {
            if ($wo->problem_type == 1) $cctv->connection_status = 0;
            if ($wo->problem_type == 2) $cctv->playback_status = 0;
            if ($wo->problem_type == 3) $cctv->replacement_status = 0;
        }
    }

    return response()->json($cctvs);
}

    public function getByBranchName($branchName)
    {
        $branch = Branch::where('name', $branchName)->firstOrFail();

        return response()->json(
            Cctv::with(['branch', 'position'])
                ->where('branch_id', $branch->id)
                ->get()
        );
    }

    public function summary()
    {
        return response()->json([
            'disconnected'     => Cctv::where('connection_status', 0)->count(),
            'playback_error'   => Cctv::where('playback_status', 0)->count(),
            'pergantian'       => Cctv::where('replacement_status', 0)->count(),
        ]);
    }
}
