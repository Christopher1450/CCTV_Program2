<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::middleware('auth')->group(function () {
    Route::apiResource('roles', RoleController::class);
});

// Login
Route::post('/login', [AuthController::class, 'login']);
// Route::middleware('auth')->group(function(){
    // Route::get('/login', [AuthController::class, 'login']);

// });

// Logout
