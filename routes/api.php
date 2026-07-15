<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CropController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);

    Route::middleware('role:admin,engineer')->group(function () {
        Route::post('/users', [AuthController::class, 'createUser']);
        
        Route::apiResource('crops', CropController::class);
        
        Route::apiResource('plants', PlantController::class);
    });

    Route::post('/predict', [DiagnosisController::class, 'predict']);
    Route::post('/chat', [ChatController::class, 'chat']);

    Route::get('/schedule', [ScheduleController::class, 'index']);
    Route::put('/schedule/{id}', [ScheduleController::class, 'update']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
});