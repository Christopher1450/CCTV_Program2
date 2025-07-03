<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use App\Models\Cctv;
use App\Models\Branch;
use Illuminate\Validation\ValidationException;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);

        $query = WorkOrder::with([
            'branch',
            'cctv',
            'takenBy',
            'cctv.position',
            'branch.cctv_type',
            'user',
            'cctv.ipCamAccount',
            'notes'
        ]);

        //  Search filter
        if ($request->has('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('cctv', function ($c) use ($keyword) {
                    $c->where('name', 'like', "%{$keyword}%");
                })
                ->orWhereHas('branch', function ($b) use ($keyword) {
                    $b->where('name', 'like', "%{$keyword}%");
                })
                ->orWhereHas('user', function ($u) use ($keyword) {
                    $u->where('username', 'like', "%{$keyword}%");
                });
            });
        }

        // Status filter
        if ($request->has('status') && in_array($request->status, [1, 2, 3, 4])) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('id', 'asc')->paginate($limit);

        $orders->getCollection()->transform(function ($order) {
            $order->has_been_replaced = WorkOrder::where('cctv_id', $order->cctv_id)
                ->where('status', 4)
                ->exists();
            return $order;
        });

        $summary = [
            'pending' => WorkOrder::where('status', 1)->count(),
            'in_progress' => WorkOrder::where('status', 2)->count(),
            'done' => WorkOrder::where('status', 3)->count(),
            'waiting_replacement' => WorkOrder::where('status', 4)->count(),
        ];

        return response()->json([
            'data'           => $orders->items(),
            'total'          => $orders->total(),
            'current_page'   => $orders->currentPage(),
            'per_page'       => $orders->perPage(),
            'last_page'      => $orders->lastPage(),
            'status_summary' => $summary,
        ]);
    }



    public function store(Request $request)
    {
        $user = Auth::user();
        $cctv = Cctv::where('name', $request->cctv_name)->first();
        $branch = Branch::where('name', $request->branch_name)->first();

        if (!$cctv || !$branch) {
            return response()->json(['message' => 'CCTV or Branch not found.'], 404);
        }

        $validated = $request->validate([
            'problem_type' => 'required|integer|in:1,2',
            'status'       => 'required|integer|in:1,2,3,4',
            'notes'        => 'required|string|max:100',
        ]);

        $existing = WorkOrder::whereHas('cctv', function ($q) use ($request) {
                $q->where('name', $request->cctv_name);
            })
            ->whereHas('branch', function ($q) use ($request) {
                $q->where('name', $request->branch_name);
            })
            ->whereIn('status', [1, 2])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'cctv_name' => 'CCTV ini sudah memiliki Work Order aktif pada cabang yang sama.',
            ]);
        }

        $workOrder = WorkOrder::create([
            'cctv_id'      => $cctv->id,
            'branch_id'    => $branch->id,
            'problem_type' => $validated['problem_type'],
            'status'       => $validated['status'],
            'notes'        => $validated['notes'],
            'taken_by'     => $user->id,
            'created_at'   => now(),
        ]);

        $this->updateCctvStatusFromWO($cctv->id);

        return response()->json($workOrder, 201);
    }


    public function show($id)
    {
        $workOrder = WorkOrder::with(['branch', 'cctv', 'takenBy', 'cctv.position','provider', 'ipCamAccount'])->findOrFail($id);

        $hasBeenReplaced = WorkOrder::where('cctv_id', $workOrder->cctv_id)
            ->where('status', 4)
            ->exists();

        return response()->json([
            'id'                => $workOrder->id,
            'user'              => $workOrder->user->username,
            'cctv_name'         => $workOrder->cctv->name,
            'branch_name'       => $workOrder->branch->name,
            'problem_type'      => $workOrder->problem_type,
            'status'            => $workOrder->status,
            'notes'             => $workOrder->notes,
            'taken_by_name'     => $workOrder->takenBy->name,
            'result_type'       => $workOrder->result_type,
            'work_order_notes'  => $workOrder->work_order_notes,
            'created_at'        => $workOrder->created_at,
            'updated_at'        => $workOrder->updated_at,
            'has_been_replaced' => $hasBeenReplaced,
        ]);
    }

    public function update(Request $request, $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'branch_id'     => 'required|exists:branches,id',
            'cctv_id'       => 'required|exists:cctvs,id',
            'title'         => 'required|string|max:100',
            'description'   => 'required|string',
            'status'        => 'nullable|integer|in:1,2,3',
            'taken_by'      => 'nullable|exists:users,id',
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
    public function takeJob($id, Request $request)
    {
        $workOrder = WorkOrder::with(['cctv.position', 'branch', 'takenBy'])->findOrFail($id);

        $user = Auth::user();

        if ($workOrder->status !== 1) {
            return response()->json(['message' => 'Job sudah diambil.'], 400);
        }

        WorkOrder::where('id',$id)->update([
            'status'        => 2,
            'taken_by'      => $user->id,
            'updated_at'    => now(),
        ]);

        $this->updateCctvStatusFromWO($workOrder->cctv_id);

        return response()->json([
            'message'   => 'Job berhasil diambil.',
            'data'      => WorkOrder::with(['cctv', 'branch', 'takenBy'])->find($workOrder->id)
        ]);
    }

    public function updateCctvStatusFromWO($cctv_id)
    {
        $latestWO = WorkOrder::where('cctv_id', $cctv_id)
            ->whereIn('status', [1, 2, 4])
            ->latest('updated_at')
            ->first();

        $defaults = [
            'connection_status' => 0,
            'playback_status' => 1,
            'replacement_status' => 1,
        ];

        if (!$latestWO) {
            Cctv::where('id', $cctv_id)->update($defaults);
            return;
        }

        $update = $defaults;

        if ($latestWO->problem_type == 1) $update['connection_status'] = 1;
        if ($latestWO->problem_type == 2) $update['playback_status'] = 0;
        if ($latestWO->status == 4)       $update['replacement_status'] = 0;

        Cctv::where('id', $cctv_id)->update($update);
    }

    public function completeJob($id, Request $request)
    {
        $status = $request->input('status');

        $rules = [
            'status'        => 'required|in:3,4',
            'result_type'   => 'required|string|in:tidak,butuh',
        ];

        if ($status == 3) {
            $rules['work_order_notes'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        $workOrder = WorkOrder::with('cctv')->findOrFail($id);
        $user = Auth::user();

        if ($workOrder->taken_by !== $user->id) {
            return response()->json(['message' => 'Kamu bukan yang mengerjakan job ini.'], 403);
        }

        $wasPreviouslyReplacement = $workOrder->status === 4;

        $workOrder->update([
            'status' => $status,
            'work_order_notes' => $request->input('work_order_notes'),
            'result_type' => $validated['result_type'],
        ]);

        if ($status == 3) {
            $cctv = $workOrder->cctv;

            if ($workOrder->problem_type == 1) {
                $cctv->connection_status = 1;
            } elseif ($workOrder->problem_type == 2) {
                $cctv->playback_status = 1;
            } elseif ($workOrder->problem_type == 3 && $validated['result_type'] === 'tidak') {
                $cctv->replacement_status = 1;
            }

            $cctv->save();
        }

        return response()->json([
            'message' => 'Job berhasil diperbarui.',
            'data' => $workOrder
        ]);
    }
    // Endpoint untuk rekap total per status
    public function summary()
    {
        return response()->json([
            'pending'     => WorkOrder::where('status', 1)->count(),
            'in_progress' => WorkOrder::where('status', 2)->count(),
            'done'        => WorkOrder::where('status', 3)->count(),
            'replacement' => WorkOrder::where('status', 4)->count(),
            'total'       => WorkOrder::count(),
        ]);
    }

    public function dashboard()
    {
        return response()->json([
            'cctvCounts' => [
                'disconnected'      => WorkOrder::where('status', 1)->count(),
                'playbackError'     => WorkOrder::where('status', 2)->count(),
                'needsReplacement'  => WorkOrder::where('status', 4)->count(),
            ],
            'lineChartData' => [
                'labels' => ['Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'],
                'data'   => [12, 8, 15, 10, 7, 5, 9]
            ],
            'latestOrders' => WorkOrder::with(['branch', 'cctv'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
        ]);
    }

    // untuk Dashboard Miniview dengan pagination per setiap 5
    public function latest(Request $request)
    {
        $limit = $request->input('limit', 5);
        $orders = WorkOrder::with(['cctv', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json($orders);
    }
}
