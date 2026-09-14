<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\EquipmentController;
use App\Http\Controllers\Api\V1\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // Tickets
        Route::apiResource('tickets', TicketController::class);
        Route::patch('tickets/{ticket}/status', [TicketController::class, 'changeStatus']);

        // Comments
        Route::get('tickets/{ticket}/comments', [CommentController::class, 'index']);
        Route::post('tickets/{ticket}/comments', [CommentController::class, 'store']);

        // Equipment
        Route::apiResource('equipment', EquipmentController::class);
        Route::patch('equipment/{equipment}/assign', [EquipmentController::class, 'assign']);

        // Dashboard
        Route::get('dashboard/stats', [DashboardController::class, 'stats']);

        // Reference data
        Route::get('departments', [DepartmentController::class, 'index']);
        Route::get('categories', [CategoryController::class, 'index']);
    });
});
