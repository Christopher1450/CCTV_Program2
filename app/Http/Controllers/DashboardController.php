<?php
namespace App\Http\Controllers;

use App\Models\Cctv;
use App\Models\WorkOrder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Status summary untuk pie chart
        $statusCounts = [
            'disconnected'       => Cctv::where('connection_status', 0)->count(),
            'playback_error'     => Cctv::where('playback_status', 0)->count(),
            'needs_replacement'  => Cctv::where('replacement_status', 0)->count(),
            'active'             => Cctv::where('connection_status', 1)
                                    ->where('playback_status', 1)
                                    ->where('replacement_status', 1)
                                    ->count(),
        ];

        // Total CCTV yang ada masalah
        $totalBroken = Cctv::where(function ($q) {
            $q->where('connection_status', 0)
            ->orWhere('playback_status', 0)
            ->orWhere('replacement_status', 0);
        })->count();

        // Work orders terbaru
        $latestWorkOrders = WorkOrder::with(['cctv', 'branch', 'takenBy'])
            ->latest('created_at')
            ->take(5)
            ->get();

        return response()->json([
            'user' => [
                'id'        => $user->id,
                'username'  => $user->username,
                'role'      => $user->role->role_name,
                'image'     => $user->image,
            ],
            'status_counts'       => $statusCounts,
            'total_broken'        => $totalBroken,
            'latest_work_orders'  => $latestWorkOrders,
        ]);
    }


    public function latestWorkOrders(Request $request)
    {
        $limit = $request->input('limit', 5);

        $orders = WorkOrder::with(['cctv', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json($orders);
    }
}
