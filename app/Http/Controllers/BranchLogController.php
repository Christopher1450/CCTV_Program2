<?php

namespace App\Http\Controllers;

use App\Models\BranchLog;
use Illuminate\Http\Request;

class BranchLogController extends Controller
{
    public function index()
    {
        return response()->json(BranchLog::with('branch')->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'log' => 'required|string|max:255',
        ]);

        $validated['created_by'] = auth()->id();

        $log = BranchLog::create($validated);

        return response()->json(['message' => 'Log created', 'data' => $log], 201);
    }
}
