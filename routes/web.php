<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');

Route::prefix('api')->group(function () {
    Route::post('/activities/start', [ActivityController::class, 'start'])->name('api.activities.start');
    Route::post('/activities/interrupt', [ActivityController::class, 'interrupt'])->name('api.activities.interrupt');
    Route::post('/activities/manual', [ActivityController::class, 'manualEntry'])->name('api.activities.manual');
    Route::post('/activities/{activity}/pause', [ActivityController::class, 'pause'])->name('api.activities.pause');
    Route::post('/activities/{activity}/resume', [ActivityController::class, 'resume'])->name('api.activities.resume');
    Route::post('/activities/{activity}/stop', [ActivityController::class, 'stop'])->name('api.activities.stop');
    Route::post('/activities/{activity}/resolve-interruption', [ActivityController::class, 'resolveInterruption'])->name('api.activities.resolve-interruption');

    Route::get('/stats/daily', [StatsController::class, 'daily'])->name('api.stats.daily');
    Route::get('/stats/heatmap', [StatsController::class, 'heatmap'])->name('api.stats.heatmap');
});

Route::resource('activities', ActivityController::class)->except(['index', 'show']);
