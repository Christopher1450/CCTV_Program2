<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cctv;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'disconnected' => Cctv::where('connection_status', 0)->count(),
            'playback_error' => Cctv::where('playback_status', 0)->count(),
            'pergantian' => Cctv::where('replacement_status', 1)->count(),
        ]);
    }
}
