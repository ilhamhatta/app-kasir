<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CafeTableController;
use App\Http\Controllers\Api\AdminOrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// admin login
Route::post('/admin/login', [AuthController::class, 'login']);

// route admin PROTECTED
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // admin logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // order management
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
    Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);

    // payment update
    Route::patch('/orders/{order}/payment', [AdminOrderController::class, 'updatePayment']);

    // cafe table management
    Route::post('/tables', [CafeTableController::class, 'store']);

    // order notification
    Route::get('/order-notification', [AdminOrderController::class, 'orderNotification']);
});

// API Routes for Menu and Orders
Route::get('/menu/{tableNumber}', [MenuController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);
