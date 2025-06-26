<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkOrderNote;
use App\Models\User;
use Illuminate\Support\Facades\Auth;



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
            'notes' => 'required|string|max:255',
        ]);

        // $validated['created_by'] = auth()->id();

        $note = WorkOrderNote::create([
        'work_order_id' => $validated['work_order_id'],
        'notes'         => $validated['notes'],
        'created_by' => Auth::id(),
    ]);
    return response()->json([
    'data' => WorkOrderNote::with(['workOrder', 'creator'])->latest()->get()
]);

    }
}
