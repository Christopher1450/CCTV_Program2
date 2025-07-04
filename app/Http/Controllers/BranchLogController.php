<?php

namespace App\Http\Controllers;

use App\Models\BranchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchLogController extends Controller
{
    public function index()
    {
        return response()->json(BranchLog::with('branch')->latest()->get());
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'log' => 'required|string|max:255',
            'created_by' => $user->id
        ]);

        $validated['created_by'] = $request->input('created_by');

        $log = BranchLog::create($validated);

        return response()->json(['message' => 'Log created', 'data' => $log], 201);
    }
}
