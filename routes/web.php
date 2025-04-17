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
use App\Http\Controllers\QCMController;
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

        // Groups & QCMs
        Route::prefix('groups')->group(function () {
            Route::get('/', [GroupController::class, 'index'])->name('group.index');

            // QCM Routes
            Route::get('/qcm/create', [GroupController::class, 'createQCM'])->name('groups.qcm.create');
            Route::post('/qcm/generate', [GroupController::class, 'generateQCM'])->name('groups.qcm.generate');
            Route::get('/qcm/{qcm}', [GroupController::class, 'showQCM'])->name('groups.qcm.show');
            Route::post('/qcm/{qcm}/assign', [GroupController::class, 'assignQCM'])->name('groups.qcm.assign');
            Route::get('/qcm/{qcm}/results', [GroupController::class, 'showResults'])->name('groups.qcm.results');
        });

        // Retro
        Route::get('retros', [RetroController::class, 'index'])->name('retro.index');

        // Common life
        Route::middleware(['auth'])->group(function () {
            Route::get('/common-life', [CommonLifeController::class, 'index'])->name('common-life.index');
            Route::post('/common-life/{task}/complete', [CommonLifeController::class, 'markAsCompleted'])->name('common-life.complete');
        });

        // Routes restricted to admin users
        Route::middleware(['auth', 'is_admin'])->group(function () {
            // Common Life Admin Routes
            Route::get('/common-life/create', [CommonLifeController::class, 'create'])->name('common-life.create');
            Route::post('/common-life', [CommonLifeController::class, 'store'])->name('common-life.store');
            Route::get('/common-life/{task}/edit', [CommonLifeController::class, 'edit'])->name('common-life.edit');
            Route::put('/common-life/{task}', [CommonLifeController::class, 'update'])->name('common-life.update');
            Route::delete('/common-life/{task}', [CommonLifeController::class, 'destroy'])->name('common-life.destroy');

            // QCM Management
            Route::prefix('qcm')->group(function () {
                Route::delete('/{qcm}', [GroupController::class, 'destroyQCM'])->name('qcm.destroy');
                Route::post('/{qcm}/update-cohorts', [GroupController::class, 'updateAssignedCohorts'])->name('qcm.update-cohorts');
                Route::get('/{qcm}/export-results', [GroupController::class, 'exportResults'])->name('qcm.export-results');
            });

            // Cohort-QCM Assignments
            Route::prefix('cohorts')->group(function () {
                Route::get('/{cohort}/bilans', [CohortController::class, 'showBilans'])->name('cohort.bilans');
                Route::post('/{cohort}/assign-bilan', [CohortController::class, 'assignBilan'])->name('cohort.assign-bilan');
            });
        });

        // Student QCM Routes
        Route::middleware(['auth', 'is_student'])->group(function () {
            Route::prefix('student')->group(function () {
                Route::get('/bilans', [StudentController::class, 'availableBilans'])->name('student.bilans');
                Route::get('/bilan/{qcm}', [StudentController::class, 'showBilan'])->name('student.show-bilan');
                Route::post('/bilan/{qcm}/submit', [StudentController::class, 'submitBilan'])->name('student.submit-bilan');
                Route::get('/bilan/{qcm}/result', [StudentController::class, 'showResult'])->name('student.show-result');
            });
        });
    });
});

require __DIR__.'/auth.php';
