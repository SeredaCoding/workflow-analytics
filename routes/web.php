<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MailReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::prefix('api')->group(function () {
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
});

require __DIR__.'/auth.php';
