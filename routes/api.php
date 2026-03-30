<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth Routes (publik)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Auth Routes (butuh token)
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
});

// Contoh: route protected lainnya
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

include __DIR__ . '/api/admin_route.php';
include __DIR__ . '/api/assistant_route.php';
include __DIR__ . '/api/instructor_route.php';
