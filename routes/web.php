<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\FaqTopicController as AdminFaqTopicController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\ProblemReportController as AdminProblemReportController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\SectorController as AdminSectorController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VersionController as AdminVersionController;
use App\Http\Controllers\Api\FaqClickController;
use App\Http\Controllers\Api\FaqController as ApiFaqController;
use App\Http\Controllers\Api\ModuleController as ApiModuleController;
use App\Http\Controllers\Api\ProblemReportController as ApiProblemReportController;
use App\Http\Controllers\Api\ProjectLinkController as ApiProjectLinkController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MailReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\VersionController;
use App\Http\Controllers\Supervisor\ProjectController as SupervisorProjectController;
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
            Route::post('/modules', [ApiModuleController::class, 'store'])->name('api.modules.store');
            
            Route::post('/activities/start', [ActivityController::class, 'start'])->name('api.activities.start');
            Route::post('/activities/interrupt', [ActivityController::class, 'interrupt'])->name('api.activities.interrupt');
            Route::post('/activities/manual', [ActivityController::class, 'manualEntry'])->name('api.activities.manual');
            Route::post('/activities/{activity}/pause', [ActivityController::class, 'pause'])->name('api.activities.pause');
            Route::post('/activities/{activity}/resume', [ActivityController::class, 'resume'])->name('api.activities.resume');
            Route::post('/activities/{activity}/reopen', [ActivityController::class, 'reopen'])->name('api.activities.reopen');
            Route::get('/activities/{activity}/detail', [ActivityController::class, 'detail'])->name('api.activities.detail');
            Route::post('/activities/{activity}/stop', [ActivityController::class, 'stop'])->name('api.activities.stop');
            Route::post('/activities/{activity}/resolve-interruption', [ActivityController::class, 'resolveInterruption'])->name('api.activities.resolve-interruption');

            Route::get('/stats/daily', [StatsController::class, 'daily'])->name('api.stats.daily');
            Route::get('/stats/yearly', [StatsController::class, 'yearly'])->name('api.stats.yearly');
            Route::get('/stats/heatmap', [StatsController::class, 'heatmap'])->name('api.stats.heatmap');
            Route::post('/reports/send-monthly', [MailReportController::class, 'send'])->name('api.reports.send-monthly');
            Route::post('/report-problem', [ApiProblemReportController::class, 'store'])->name('api.report-problem');
            Route::get('/faqs', [ApiFaqController::class, 'index'])->name('api.faqs.index');
            Route::post('/faqs/{faq}/click', FaqClickController::class)->name('api.faqs.click');
        });

        Route::resource('activities', ActivityController::class)->except(['index', 'show', 'create', 'edit']);

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::post('/projects/{project}/links', [ApiProjectLinkController::class, 'store'])->name('api.projects.links.store');
        Route::delete('/projects/{project}/links/{link}', [ApiProjectLinkController::class, 'destroy'])->name('api.projects.links.destroy');
        Route::post('/projects/{project}/links/{link}/refresh', [ApiProjectLinkController::class, 'refresh'])->name('api.projects.links.refresh');

        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
            Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
            Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
            Route::get('/sectors', [AdminSectorController::class, 'index'])->name('sectors.index');
            Route::post('/sectors', [AdminSectorController::class, 'store'])->name('sectors.store');
            Route::get('/sectors/{sector}/edit', [AdminSectorController::class, 'edit'])->name('sectors.edit');
            Route::put('/sectors/{sector}', [AdminSectorController::class, 'update'])->name('sectors.update');
            Route::delete('/sectors/{sector}', [AdminSectorController::class, 'destroy'])->name('sectors.destroy');
            Route::get('/projects', [AdminProjectController::class, 'index'])->name('projects.index');
            Route::post('/projects', [AdminProjectController::class, 'store'])->name('projects.store');
            Route::put('/projects/{project}', [AdminProjectController::class, 'update'])->name('projects.update');
            Route::delete('/projects/{project}', [AdminProjectController::class, 'destroy'])->name('projects.destroy');
            Route::get('/modules', [AdminModuleController::class, 'index'])->name('modules.index');
            Route::post('/modules', [AdminModuleController::class, 'store'])->name('modules.store');
            Route::put('/modules/{module}', [AdminModuleController::class, 'update'])->name('modules.update');
            Route::delete('/modules/{module}', [AdminModuleController::class, 'destroy'])->name('modules.destroy');
            Route::get('/faqs', [AdminFaqController::class, 'index'])->name('faqs.index');
            Route::post('/faqs', [AdminFaqController::class, 'store'])->name('faqs.store');
            Route::put('/faqs/{faq}', [AdminFaqController::class, 'update'])->name('faqs.update');
            Route::delete('/faqs/{faq}', [AdminFaqController::class, 'destroy'])->name('faqs.destroy');
            Route::get('/faq-topics', [AdminFaqTopicController::class, 'index'])->name('faq-topics.index');
            Route::post('/faq-topics', [AdminFaqTopicController::class, 'store'])->name('faq-topics.store');
            Route::put('/faq-topics/{topic}', [AdminFaqTopicController::class, 'update'])->name('faq-topics.update');
            Route::delete('/faq-topics/{topic}', [AdminFaqTopicController::class, 'destroy'])->name('faq-topics.destroy');
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::get('/problem-reports', [AdminProblemReportController::class, 'index'])->name('problem-reports.index');
            Route::put('/problem-reports/{report}', [AdminProblemReportController::class, 'update'])->name('problem-reports.update');
            Route::get('/versions', [AdminVersionController::class, 'index'])->name('versions.index');
            Route::post('/versions', [AdminVersionController::class, 'store'])->name('versions.store');
            Route::put('/versions/{version}', [AdminVersionController::class, 'update'])->name('versions.update');
            Route::delete('/versions/{version}', [AdminVersionController::class, 'destroy'])->name('versions.destroy');
        });

        Route::middleware('supervisor')->prefix('supervisor')->name('supervisor.')->group(function () {
            Route::get('/sector', [SupervisorSectorController::class, 'index'])->name('sector.index');
            Route::get('/users/{user}', [SupervisorUserReportController::class, 'show'])->name('users.show');
            Route::get('/projects', [SupervisorProjectController::class, 'index'])->name('projects.index');
            Route::get('/projects/{project}', [SupervisorProjectController::class, 'show'])->name('projects.show');
            Route::post('/projects', [SupervisorProjectController::class, 'store'])->name('projects.store');
            Route::put('/projects/{project}', [SupervisorProjectController::class, 'update'])->name('projects.update');
            Route::delete('/projects/{project}', [SupervisorProjectController::class, 'destroy'])->name('projects.destroy');
            Route::post('/projects/{project}/assign-user', [SupervisorProjectController::class, 'assignUser'])->name('projects.assign-user');
            Route::delete('/projects/{project}/users/{user}', [SupervisorProjectController::class, 'removeUser'])->name('projects.remove-user');
        });

        Route::get('/admin/problem-reports/{report}/images/{index}', [AdminProblemReportController::class, 'showImage'])
            ->name('admin.problem-reports.images');

        Route::get('/versions', [VersionController::class, 'index'])->name('versions.index');
    });

require __DIR__.'/auth.php';
