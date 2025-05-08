<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CctvPosition;

class CctvPositionController extends Controller
{
    public function index()
    {
        return response()->json(CctvPosition::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:cctv_positions,name',
        ]);

        $position = CctvPosition::create($validated);

        return response()->json([
            'message' => 'Position created successfully',
            'data' => $position
        ], 201);
    }
}
