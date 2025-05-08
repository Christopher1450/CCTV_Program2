<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use Illuminate\Http\Request;

class CctvController extends Controller
{
    public function index()
    {
        return response()->json(Cctv::with(['branch', 'position'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'cctv_position_id' => 'required|exists:cctv_positions,id',
            'name' => 'required|string',
            'is_active' => 'boolean',
            'connection_status' => 'in:0,1',
            'playback_status' => 'in:0,1',
            'replacement_status' => 'in:0,1',
        ]);

        $cctv = Cctv::create($validated);

        return response()->json($cctv, 201);
    }

    public function show($id)
    {
        $cctv = Cctv::with(['branch', 'position'])->findOrFail($id);
        return response()->json($cctv);
    }

    public function update(Request $request, $id)
    {
        $cctv = Cctv::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'cctv_position_id' => 'required|exists:cctv_positions,id',
            'name' => 'required|string',
            'is_active' => 'boolean',
            'connection_status' => 'in:0,1',
            'playback_status' => 'in:0,1',
            'replacement_status' => 'in:0,1',
        ]);

        $cctv->update($validated);

        return response()->json($cctv);
    }

    public function destroy($id)
    {
        $cctv = Cctv::findOrFail($id);
        $cctv->delete();

        return response()->json(['message' => 'CCTV deleted successfully']);
    }
}
