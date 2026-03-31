<?php

use Illuminate\Support\Facades\Route;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Student Actions
    Route::post('/submit-proposal', [DashboardController::class, 'submitProposal'])->name('thesis.submit_proposal');
    Route::post('/upload-draft', [DashboardController::class, 'uploadDraft'])->name('thesis.upload');
    Route::post('/submit-logbook', [DashboardController::class, 'submitLogbook'])->name('thesis.logbook');
    
    // Lecturer Actions
    Route::post('/review-thesis', [DashboardController::class, 'reviewThesis'])->name('thesis.review');
    Route::post('/approve-logbook', [DashboardController::class, 'approveLogbook'])->name('thesis.logbook.approve');
    
    // Admin Actions
    Route::post('/assign-lecturers', [DashboardController::class, 'assignLecturers'])->name('admin.assign_lecturers');
    Route::post('/schedule-exam', [DashboardController::class, 'scheduleExam'])->name('admin.schedule_exam');
    Route::post('/mark-graduated', [DashboardController::class, 'markAsGraduated'])->name('admin.mark_graduated');
});

// Redirect root
Route::get('/', function () {
    return redirect()->route('login');
});