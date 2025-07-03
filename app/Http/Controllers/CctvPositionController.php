<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CctvPosition;
// use auth

class CctvPositionController extends Controller
{
    public function index(Request $request)
{
    $limit = $request->input('limit', 10);

    $query = CctvPosition::query();

    if ($request->has('q') && !empty($request->q)) {
        $keyword = strtolower($request->q);
        $query->whereRaw('LOWER(name) LIKE ?', ["%{$keyword}%"]);
    }

    $positions = $query->orderBy('id', 'asc')->paginate($limit);

    return response()->json([
        'data'          => $positions->items(),
        'total'         => $positions->total(),
        'current_page'  => $positions->currentPage(),
        'last_page'     => $positions->lastPage(),
    ]);
}



    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|unique:cctv_positions,name',
        ]);

        $validated['created_by'] = $user->id;
        // $validated['updated_by'] = $user->id;

        // $provider = InternetProvider::create($validated);

        $position = CctvPosition::create($validated);

        return response()->json([
            'message' => 'Position created successfully',
            'data' => $position
        ], 201);
    }
    public function update(Request $request, CctvPosition $cctvPosition)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|unique:cctv_positions,name,' . $cctvPosition->id,
        ]);

        $validated['updated_by'] = $user->id;

        $cctvPosition->update($validated);

        return response()->json([
            'message' => 'Position updated successfully',
            'data' => $cctvPosition
        ]);
    }
    public function destroy(CctvPosition $cctvPosition)
    {
        $cctvPosition->delete();

        return response()->json([
            'message' => 'Position deleted successfully'
        ]);
    }
}
