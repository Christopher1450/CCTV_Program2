<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkOrderNote;

class WorkOrderNoteController extends Controller
{
    public function index()
    {
        return response()->json(WorkOrderNote::with('workOrder')->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'note' => 'required|string|max:255',
        ]);

        // $validated['created_by'] = auth()->id();

        $note = WorkOrderNote::create($validated);

        return response()->json([
            'message' => 'Work order note created',
            'data' => $note
        ], 201);
    }
}
