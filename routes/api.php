<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\InternetProviderController;
use App\Http\Controllers\IpCamAccountController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchLogController;
use App\Http\Controllers\CctvController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\WorkOrderNoteController;
use App\Http\Controllers\CctvPositionController;
use App\Http\Controllers\CctvNoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Models\WorkOrderNote;
use App\Models\WorkOrder;
use Illuminate\Http\Request;



Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('throttle:5,1');
    Route::post('/logout', [AuthController::class, 'logout']);
});

// user -> name
Route::middleware('auth:api')->get('/me', function () {
    return response()->json(['data' => \Illuminate\Support\Facades\Auth::user()]);
});

Route::middleware('auth:api')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/work-orders/latest', [DashboardController::class, 'latestWorkOrders']);
    Route::middleware('auth:api')->get('/work-orders/latest', [WorkOrderController::class, 'latest']);

});

Route::middleware('auth:api')->group(function () {
    // Route::get('/dashboard', action: [DashboardController::class, 'index']);
    Route::get('/cctvs/branches', [CctvController::class, 'getUniqueBranches']);
    Route::get('/cctvs/branch/{id}', [CctvController::class, 'getByBranch']);
    Route::get('/cctvs/branch/{branch_id}', [CctvController::class, 'getByBranch']);
    Route::get('/cctvs/by-branch-name/{name}', [CctvController::class, 'getByBranchName']);
    Route::post('/cctvs', [CctvController::class, 'store']);
});

Route::middleware('auth:api')->group(function () {
    // Users
    Route::apiResource('users', UserController::class);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index']);
    // Logout (optional)
    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::get('/cctvs/search', [CctvController::class, 'searchByName'])->middleware('auth:api');
Route::get('/branches/search', [BranchController::class, 'searchByName'])->middleware('auth:api');
Route::middleware('auth:api')->get('/branches/sync/{id}', [BranchController::class, 'syncFromHRIS']);


Route::apiResource('internet-providers', InternetProviderController::class)->middleware('auth:api');
route::get('/internet-providers/search', [InternetProviderController::class, 'searchByName'])->middleware('auth:api');
route::delete('/internet-providers/{id}/delete', [InternetProviderController::class, 'destroy'])->middleware('auth:api');

Route::apiResource('ipcam-accounts', IpCamAccountController::class)->middleware('auth:api');

Route::middleware('auth:api')->group(function () {
    Route::apiResource('branches', BranchController::class);
    Route::get('/branches/sync/{id}', [BranchController::class, 'syncFromHRIS']);
    Route::get('/branches/search', [BranchController::class, 'searchByName']);
    Route::apiResource('branches-logs', BranchLogController::class)->middleware('auth:api');
});

Route::apiResource('branches', BranchController::class)->middleware('auth:api');
Route::apiResource('branches-logs', BranchLogController::class)->middleware('auth:api');

Route::apiResource('cctvs', CctvController::class)->middleware('auth:api');
Route::get('cctv/detail/{id}', [CctvController::class, 'show'])->middleware('auth:api');
Route::post('/cctv/detail{id}}',[CctvController::class ])->middleware('auth:api');
// Route::get('cctv/detail/{id}', CctvController::class)->middleware('auth:api');
Route::apiResource('cctv-positions', CctvPositionController::class)->middleware('auth:api');
Route::apiResource('cctv-notes', CctvNoteController::class)->middleware('auth:api');

Route::apiResource('work-orders', WorkOrderController::class)->middleware('auth:api');
// Route::post('/work-orders',action: WorkOrderController::class)->middleware('auth:api');
Route::put('/work-orders/{id}/take', [WorkOrderController::class, 'takeJob'])->middleware('auth:api');
Route::put('/work-orders/{id}/complete', [WorkOrderController::class, 'completeJob'])->middleware('auth:api');
Route::apiResource('work_order_notes', WorkOrderNoteController::class)->middleware('auth:api');

Route::get('/work-orders/{id}/notes', function ($id) {
    return response()->json([
        'data' => WorkOrderNote::with('creator')
            ->where('work_order_id', $id)
            ->latest()
            ->get(),
    ]);
});
// buat dashbaord Mini view
Route::get('/work-orders/latest', [WorkOrderController::class, 'latest']);



Route::apiResource('roles', RoleController::class)->middleware('auth:api');

Route::apiResource('cctv-positions', CctvPositionController::class)->middleware('auth:api');




