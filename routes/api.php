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
use App\Http\Controllers\Api\Employee\RosterController;
use App\Http\Controllers\Api\Client\ServiceRequestController as ClientRequest;
use App\Http\Controllers\Api\Admin\AssignmentController as AdminAssignment;
use App\Http\Controllers\Api\Admin\ClientController;
use App\Http\Controllers\Api\Auth\RegisterController;

// ====================================
// PUBLIC ROUTES (NO LOGIN REQUIRED)
// ====================================

Route::get('/test', fn() => response()->json(['message' => 'API IS WORKING!']));

// Categories & Centers (public)
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/count', [CategoryController::class, 'count']);
Route::get('/categories/{category}', [CategoryController::class, 'show']); // GET /api/categories/{id} (single)

Route::post('/register', [RegisterController::class, 'register']);


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
    Route::prefix('ai')->group(function () {
    Route::post('/blog/generate', [AiBlogController::class, 'generate'])->name('ai.blog.generate');
});

    Route::apiResource('posts', BlogPostController::class)
        ->except(['index', 'show', 'create', 'edit']);
    Route::delete('/posts/{post:uuid}', [BlogPostController::class, 'destroy']);
    Route::post('posts/{post}/publish', [BlogPostController::class, 'publish']);
    Route::post('posts/{post}/unpublish', [BlogPostController::class, 'unpublish']);
    Route::apiResource('employees', EmployeeController::class);




    Route::middleware('auth:sanctum')->group(function () {
    // Leave Requests
    Route::get('leaves/{leaveRequest}', [LeaveRequestController::class, 'show']);
    Route::get('leave-types', [LeaveRequestController::class, 'getTypes']);
    Route::get('leaves', [LeaveRequestController::class, 'index']); // Admin view
    Route::post('leaves', [LeaveRequestController::class, 'store']); // Employee apply
    Route::patch('leaves/{leaveRequest}/status', [LeaveRequestController::class, 'updateStatus']); // Admin action
    Route::get('my-leaves', [LeaveRequestController::class, 'myHistory']);
    Route::get('leave-balances', [LeaveRequestController::class, 'myBalances']);
    Route::delete('leaves/{leaveRequest}', [LeaveRequestController::class, 'destroy']);
    // Stats for the Top Cards
    Route::get('leave-stats', function() {
        return [
            'total' => \App\Models\LeaveRequest::count(),
            'approved' => \App\Models\LeaveRequest::where('status', 'approved')->count(),
            'pending' => \App\Models\LeaveRequest::where('status', 'pending')->count(),
            'rejected' => \App\Models\LeaveRequest::where('status', 'rejected')->count(),
        ];
    });


    Route::prefix('admin')->group(function () {
        // List all requests from all companies
        Route::get('/requests', [AdminAssignment::class, 'index']); 
        // Assign Security Staff (The method we just fixed)
        Route::patch('/requests/{uuid}/assign', [AdminAssignment::class, 'assignStaff']); 
        // Update status (e.g., mark as 'active' or 'completed')
        Route::get('/requests/{uuid}', [AdminAssignment::class, 'show']);
        Route::patch('/requests/{uuid}/status', [AdminAssignment::class, 'updateStatus']); 
        Route::get('/clients', [ClientController::class, 'index']);
        Route::post('/clients', [ClientController::class, 'store']);
        Route::get('/clients/{uuid}', [ClientController::class, 'show']);
        Route::put('/clients/{uuid}', [ClientController::class, 'update']);
        Route::delete('/clients/{uuid}', [ClientController::class, 'destroy']);
    
    });



        Route::prefix('client')->group(function () {
        // Submit the Figma form
        Route::post('/requests', [ClientRequest::class, 'store']); 
        // View their own history
        Route::get('/requests', [ClientRequest::class, 'index']); 
        // Track a specific request progress
        Route::get('/requests/{uuid}', [ClientRequest::class, 'show']); 
    });


    Route::prefix('employee')->group(function () {
    Route::get('/roster', [RosterController::class, 'index']);
    // For clocking in/out or status updates
    Route::patch('/assignments/{uuid}/confirm', [RosterController::class, 'confirm']);
    Route::patch('/roster/{uuid}/report', [RosterController::class, 'updateReport']); 
});

});

});
