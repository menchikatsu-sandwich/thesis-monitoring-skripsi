<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;

// ============================
// GUEST
// ============================
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthApiController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthApiController::class, 'login']);

    Route::get('/register', [AuthApiController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthApiController::class, 'register']);
});

// ============================
// AUTH
// ============================
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthApiController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardApiController::class, 'dashboard'])->name('dashboard');

    // Student
    Route::post('/submit-proposal', [DashboardApiController::class, 'submitProposal'])->name('thesis.submit_proposal');
    Route::post('/upload-draft', [DashboardApiController::class, 'uploadDraft'])->name('thesis.upload');

    // Lecturer
    Route::post('/review-thesis', [DashboardApiController::class, 'reviewThesis'])->name('thesis.review');

    // Admin
    Route::post('/assign-lecturers', [DashboardApiController::class, 'assignLecturers'])->name('admin.assign_lecturers');
    Route::post('/schedule-exam', [DashboardApiController::class, 'scheduleExam'])->name('admin.schedule_exam');
    Route::post('/mark-graduated', [DashboardApiController::class, 'markAsGraduated'])->name('admin.mark_graduated');
});

// ============================
// ROOT
// ============================
Route::get('/', function () {
    return redirect()->route('login');
});