<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\SectorController as AdminSectorController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MailReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\Supervisor\SectorController as SupervisorSectorController;
use App\Http\Controllers\Supervisor\UserReportController as SupervisorUserReportController;
use Illuminate\Support\Facades\Route;

    Route::middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
        Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::prefix('api')->group(function () {
            Route::post('/categories', [CategoryController::class, 'store'])->name('api.categories.store');
            Route::post('/projects', [ProjectController::class, 'store'])->name('api.projects.store');
            
            Route::post('/activities/start', [ActivityController::class, 'start'])->name('api.activities.start');
            Route::post('/activities/interrupt', [ActivityController::class, 'interrupt'])->name('api.activities.interrupt');
            Route::post('/activities/manual', [ActivityController::class, 'manualEntry'])->name('api.activities.manual');
            Route::post('/activities/{activity}/pause', [ActivityController::class, 'pause'])->name('api.activities.pause');
            Route::post('/activities/{activity}/resume', [ActivityController::class, 'resume'])->name('api.activities.resume');
            Route::post('/activities/{activity}/stop', [ActivityController::class, 'stop'])->name('api.activities.stop');
            Route::post('/activities/{activity}/resolve-interruption', [ActivityController::class, 'resolveInterruption'])->name('api.activities.resolve-interruption');

            Route::get('/stats/daily', [StatsController::class, 'daily'])->name('api.stats.daily');
            Route::get('/stats/yearly', [StatsController::class, 'yearly'])->name('api.stats.yearly');
            Route::get('/stats/heatmap', [StatsController::class, 'heatmap'])->name('api.stats.heatmap');
            Route::post('/reports/send-monthly', [MailReportController::class, 'send'])->name('api.reports.send-monthly');
        });

        Route::resource('activities', ActivityController::class)->except(['index', 'show']);

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/sectors', [AdminSectorController::class, 'index'])->name('sectors.index');
            Route::post('/sectors', [AdminSectorController::class, 'store'])->name('sectors.store');
            Route::get('/sectors/{sector}/edit', [AdminSectorController::class, 'edit'])->name('sectors.edit');
            Route::put('/sectors/{sector}', [AdminSectorController::class, 'update'])->name('sectors.update');
            Route::delete('/sectors/{sector}', [AdminSectorController::class, 'destroy'])->name('sectors.destroy');
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        });

        Route::middleware('supervisor')->prefix('supervisor')->name('supervisor.')->group(function () {
            Route::get('/sector', [SupervisorSectorController::class, 'index'])->name('sector.index');
            Route::get('/users/{user}', [SupervisorUserReportController::class, 'show'])->name('users.show');
        });
    });

require __DIR__.'/auth.php';
