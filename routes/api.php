<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/users', [DashboardController::class, 'apiGetUsers']);
Route::get('/users/{id}', [DashboardController::class, 'apiGetUser']);
Route::post('/users', [DashboardController::class, 'apiStoreUser']);
Route::put('/users/{id}', [DashboardController::class, 'apiUpdateUser']);
Route::delete('/users/{id}', [DashboardController::class, 'apiDeleteUser']);
Route::get('/thesis', [DashboardController::class, 'apiGetThesis']);
Route::get('/thesis/{id}', [DashboardController::class, 'apiGetThesisById']);
Route::get('/dashboard', [DashboardController::class, 'index']);