<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CropController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiseaseController;
use Illuminate\Support\Facades\Route;


Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/password', [AuthController::class, 'resetPassword']);

    Route::apiResource('diseases', DiseaseController::class)->only(['index', 'show']);
    Route::apiResource('crops', CropController::class)->only(['index', 'show']);

    Route::apiResource('plants', PlantController::class);
    Route::post('/plants/{plant}/harvest', [PlantController::class, 'harvest']);
    Route::post('/plants/{plant}/undo-harvest', [PlantController::class, 'undoHarvest']);

    Route::post('/predict', [DiagnosisController::class, 'predict']);
    Route::post('/chat', [ChatController::class, 'chat']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/schedule', [ScheduleController::class, 'index']);
    Route::post('/schedule/{schedule}/irrigate', [ScheduleController::class, 'irrigate']);
    Route::post('/schedule/{plant}/undo', [ScheduleController::class, 'undo']);
    Route::put('/schedule/{schedule}/reschedule', [ScheduleController::class, 'reschedule']);

    Route::middleware('role:admin,engineer')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('diseases', DiseaseController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('crops', CropController::class)->only(['store', 'update', 'destroy']);

        Route::get('/audit-logs', [AuditLogController::class, 'index']);
    });
});
