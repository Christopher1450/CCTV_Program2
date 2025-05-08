<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function index()
    {
        return response()->json(WorkOrder::with(['branch', 'cctv', 'takenBy'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'cctv_id' => 'required|exists:cctvs,id',
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'status' => 'in:open,in_progress,closed',
            'taken_by' => 'nullable|exists:users,id',
        ]);

        $workOrder = WorkOrder::create($validated);

        return response()->json($workOrder, 201);
    }

    public function show($id)
    {
        $workOrder = WorkOrder::with(['branch', 'cctv', 'takenBy'])->findOrFail($id);
        return response()->json($workOrder);
    }

    public function update(Request $request, $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'cctv_id' => 'required|exists:cctvs,id',
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'status' => 'in:open,in_progress,closed',
            'taken_by' => 'nullable|exists:users,id',
        ]);

        $workOrder->update($validated);

        return response()->json($workOrder);
    }

    public function destroy($id)
    {
        $workOrder = WorkOrder::findOrFail($id);
        $workOrder->delete();

        return response()->json(['message' => 'Work Order deleted successfully']);
    }
}
