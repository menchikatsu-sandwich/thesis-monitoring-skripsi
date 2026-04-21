<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;

// ============================
// PUBLIC
// ============================
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);

// ============================
// PROTECTED (SANCTUM)
// ============================
Route::middleware('auth:sanctum')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardApiController::class, 'dashboard']);

    // Thesis
    Route::prefix('thesis')->group(function () {
        Route::get('/', [DashboardApiController::class, 'getThesis']);
        Route::get('/{id}', [DashboardApiController::class, 'getThesisById']);

        Route::post('/proposal', [DashboardApiController::class, 'submitProposal']);
        Route::post('/upload', [DashboardApiController::class, 'uploadDraft']);
        Route::post('/review', [DashboardApiController::class, 'reviewThesis']);

        Route::post('/assign', [DashboardApiController::class, 'assignLecturers']);
        Route::post('/schedule', [DashboardApiController::class, 'scheduleExam']);
        Route::post('/graduate', [DashboardApiController::class, 'markAsGraduated']);

        Route::delete('/{id}', [DashboardApiController::class, 'deleteThesis']);
    });

    // Users
    Route::prefix('users')->group(function () {
        Route::get('/', [DashboardApiController::class, 'getUsers']);
        Route::get('/{id}', [DashboardApiController::class, 'getUser']);

        Route::post('/', [DashboardApiController::class, 'storeUser']);
        Route::put('/{id}', [DashboardApiController::class, 'updateUser']);
        Route::delete('/{id}', [DashboardApiController::class, 'deleteUser']);
    });

    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/me', [AuthApiController::class, 'me']);
});