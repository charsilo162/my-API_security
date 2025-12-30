<?php

use App\Http\Controllers\Api\AiBlogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogCategoryController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\BlogPostController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LeaveRequestController;

// ====================================
// PUBLIC ROUTES (NO LOGIN REQUIRED)
// ====================================

Route::get('/test', fn() => response()->json(['message' => 'API IS WORKING!']));

// Categories & Centers (public)
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/count', [CategoryController::class, 'count']);
Route::get('/categories/{category}', [CategoryController::class, 'show']); // GET /api/categories/{id} (single)




Route::get('courses/count', [CategoryController::class, 'count']);


// Auth (public)
Route::post('/login', [AuthController::class, 'login']);
Route::get('/categories', [BlogCategoryController::class, 'index']);


Route::get('/payment/callback', [PaymentApiController::class, 'callback']);
Route::get('/payment/callback', [PaymentApiController::class, 'callback']);

/*
|--------------------------------------------------------------------------
| PUBLIC BLOG ROUTES
|--------------------------------------------------------------------------
*/
Route::apiResource('posts', BlogPostController::class)
    ->only(['index', 'show']);

/*
|--------------------------------------------------------------------------
| ADMIN BLOG ROUTES (SANCTUM)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('posts', BlogPostController::class)
        ->except(['index', 'show', 'create', 'edit']);
    Route::delete('/posts/{post:uuid}', [BlogPostController::class, 'destroy']);
    Route::post('posts/{post}/publish', [BlogPostController::class, 'publish']);
    Route::post('posts/{post}/unpublish', [BlogPostController::class, 'unpublish']);
    Route::apiResource('employees', EmployeeController::class);




    Route::middleware('auth:sanctum')->group(function () {
    // Leave Requests
    Route::get('leaves', [LeaveRequestController::class, 'index']); // Admin view
    Route::post('leaves', [LeaveRequestController::class, 'store']); // Employee apply
    Route::patch('leaves/{leaveRequest}/status', [LeaveRequestController::class, 'updateStatus']); // Admin action
    Route::get('my-leaves', [LeaveRequestController::class, 'myHistory']);
    Route::get('leave-balances', [LeaveRequestController::class, 'myBalances']);
    // Stats for the Top Cards
    Route::get('leave-stats', function() {
        return [
            'total' => \App\Models\LeaveRequest::count(),
            'approved' => \App\Models\LeaveRequest::where('status', 'approved')->count(),
            'pending' => \App\Models\LeaveRequest::where('status', 'pending')->count(),
            'rejected' => \App\Models\LeaveRequest::where('status', 'rejected')->count(),
        ];
    });
});
});
