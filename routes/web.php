<?php

use App\Http\Controllers\CohortController;
use App\Http\Controllers\CommonLifeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RetroController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\TeacherController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

// Redirect the root path to /dashboard
Route::redirect('/', 'dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('verified')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Cohorts
        Route::get('/cohorts', [CohortController::class, 'index'])->name('cohort.index');
        Route::get('/cohort/{cohort}', [CohortController::class, 'show'])->name('cohort.show');

        // Teachers
        Route::get('/teachers', [TeacherController::class, 'index'])->name('teacher.index');

        // Students
        Route::get('students', [StudentController::class, 'index'])->name('student.index');

        // Knowledge
        Route::get('knowledge', [KnowledgeController::class, 'index'])->name('knowledge.index');

        // Groups
        Route::get('groups', [GroupController::class, 'index'])->name('group.index');

        // Retro
        route::get('retros', [RetroController::class, 'index'])->name('retro.index');

        // Common life

        // Routes accessible to any authenticated user
        Route::middleware(['auth'])->group(function () {
            // View tasks (to-do and completed)
            Route::get('/common-life', [CommonLifeController::class, 'index'])->name('common-life.index');

            // Mark a task as completed with an optional comment
            Route::post('/common-life/{task}/complete', [CommonLifeController::class, 'markAsCompleted'])->name('common-life.complete');
        });

        // Routes restricted to admin users
        Route::middleware(['auth', 'is_admin'])->group(function () {
            Route::get('/common-life/create', [CommonLifeController::class, 'create'])->name('common-life.create');
            Route::post('/common-life', [CommonLifeController::class, 'store'])->name('common-life.store');

            Route::get('/common-life/{task}/edit', [CommonLifeController::class, 'edit'])->name('common-life.edit');
            Route::put('/common-life/{task}', [CommonLifeController::class, 'update'])->name('common-life.update');

            Route::delete('/common-life/{task}', [CommonLifeController::class, 'destroy'])->name('common-life.destroy');
        });


    });

});

require __DIR__.'/auth.php';
