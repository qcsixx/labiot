<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BorrowRequestController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemTrackingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReturnRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\UserMiddleware;
use App\Http\Middleware\EnsureTokenIsValid;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Admin protected routes
Route::middleware(['auth:web_admin', AdminMiddleware::class])->group(function () {
    Route::get('/admin/dashboard', function () {
        return response()->json(['message' => 'Admin Dashboard']);
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::apiResource('items', ItemController::class);
    Route::apiResource('item-tracking', ItemTrackingController::class)->except(['update']);
    Route::post('/notifications/return-warnings', [NotificationController::class, 'generateReturnWarnings']);
    Route::get('/returns/pending', [ReturnRequestController::class, 'pendingReturns']);
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'show']);
    Route::post('/returns/{borrowRequest}', [ReturnRequestController::class, 'processReturn']);
});

// User protected routes
Route::middleware(['auth:web_user', UserMiddleware::class])->group(function () {
    Route::get('/user/dashboard', function () {
        return response()->json(['message' => 'User Dashboard']);
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::apiResource('borrow-requests', BorrowRequestController::class);
    Route::delete('/borrow-requests/{borrowRequest}/cancel', [BorrowRequestController::class, 'cancel']);
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'show']);
    Route::post('/returns/{borrowRequest}', [ReturnRequestController::class, 'processReturn']);
}); 