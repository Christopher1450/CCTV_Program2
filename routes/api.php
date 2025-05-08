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

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->get('/user', [AuthController::class, 'me']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::apiResource('internet-providers', InternetProviderController::class)->middleware('auth:api');

Route::apiResource('ipcam-accounts', IpCamAccountController::class)->middleware('auth:api');

Route::apiResource('branches', BranchController::class)->middleware('auth:api');
Route::apiResource('branches-logs', BranchLogController::class)->middleware('auth:api');

Route::apiResource('cctvs', CctvController::class)->middleware('auth:api');
Route::apiResource('cctv-positions', CctvPositionController::class)->middleware('auth:api');
Route::apiResource('cctv-notes', CctvNoteController::class)->middleware('auth:api');

Route::apiResource('work-orders', WorkOrderController::class)->middleware('auth:api');
Route::apiResource('work-order-notes', WorkOrderNoteController::class)->middleware('auth:api');

