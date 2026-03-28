<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;

// Guest Routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Auth Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard Utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Mahasiswa
    Route::post('/submit-proposal', [DashboardController::class, 'submitProposal'])->name('thesis.submit_proposal');
    Route::post('/upload-draft', [DashboardController::class, 'uploadDraft'])->name('thesis.upload');
    
    // Dosen
    Route::post('/review-thesis', [DashboardController::class, 'reviewThesis'])->name('thesis.review');
    
    // Admin
    Route::post('/assign-lecturers', [DashboardController::class, 'assignLecturers'])->name('admin.assign');
    Route::post('/schedule-exam', [DashboardController::class, 'scheduleExam'])->name('admin.schedule');
});

// Redirect root
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});