<?php

use App\Http\Controllers\CohortController;
use App\Http\Controllers\CommonLifeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RetroController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
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
        Route::prefix('knowledge')->group(function () {
            Route::get('/', [KnowledgeController::class, 'index'])->name('knowledge.index');
            Route::get('/create', [KnowledgeController::class, 'create'])->name('knowledge.create');
            Route::post('/generate', [KnowledgeController::class, 'generate'])->name('knowledge.generate');
            Route::get('/{qcm}', [KnowledgeController::class, 'show'])->name('knowledge.show'); // Pour afficher un bilan spécifique
            Route::get('/attempt/{qcm}', [KnowledgeController::class, 'attempt'])->name('knowledge.attempt'); // Afficher le QCM pour répondre
            Route::post('/submit/{qcm}', [KnowledgeController::class, 'submit'])->name('knowledge.submit'); // Soumettre les réponses
        });

        // Retro
        Route::get('retros', [RetroController::class, 'index'])->name('retro.index');

        // Group
        Route::get('groups', [GroupController::class, 'index'])->name('group.index');

        // Common life
        Route::prefix('common-life')->group(function () {
            Route::get('/', [CommonLifeController::class, 'index'])->name('common-life.index');
            Route::post('/{task}/complete', [CommonLifeController::class, 'markAsCompleted'])->name('common-life.complete');
        });

        // Admin Restricted Routes
        Route::middleware(['auth', 'is_admin'])->group(function () {
            // Common Life Admin Routes
            Route::prefix('common-life')->group(function () {
                Route::get('/create', [CommonLifeController::class, 'create'])->name('common-life.create');
                Route::post('/', [CommonLifeController::class, 'store'])->name('common-life.store');
                Route::get('/{task}/edit', [CommonLifeController::class, 'edit'])->name('common-life.edit');
                Route::put('/{task}', [CommonLifeController::class, 'update'])->name('common-life.update');
                Route::delete('/{task}', [CommonLifeController::class, 'destroy'])->name('common-life.destroy');
            });

            // QCM Routes (Knowledge)
            Route::prefix('knowledge')->group(function () {
                Route::get('/create', [KnowledgeController::class, 'create'])->name('knowledge.create');
                Route::post('/generate', [KnowledgeController::class, 'generate'])->name('knowledge.generate');
                Route::get('/{qcm}', [KnowledgeController::class, 'show'])->name('knowledge.show'); // Pour afficher un bilan spécifique
            });
        });
    });
});

require __DIR__.'/auth.php';
