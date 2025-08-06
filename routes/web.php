<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\DelateController;
use App\Models\User;
use App\Models\Admin;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Rute untuk Sisi Umum (Publik) ---
// Rute ini bisa diakses tanpa login
Route::get('/', [MainController::class, 'publicDashboard'])->name('public.dashboard');
Route::get('/diskusi', [MainController::class, 'welcome'])->name('public.diskusi');
Route::get('/capaian', [MainController::class, 'capaian'])->name('public.capaian');
Route::get('/materi/{class?}', [MainController::class, 'showPublicMaterials'])->name('public.materi');
Route::get('/stories', [StoryController::class, 'showPublicStoriesPage'])->name('public.stories');
Route::get('/api/stories/active', [StoryController::class, 'getActiveStories'])->name('api.stories.active');
Route::get('/tugas', [MainController::class, 'showTugas'])->name('public.tugas');
Route::get('/tugas/{task}', [MainController::class, 'showStudentTaskDetail'])->name('student.task.show');
Route::post('/tugas/{task}/submit', [StoreController::class, 'submitTask'])->name('student.task.submit');



// --- Rute untuk Sisi Admin ---
Route::prefix('admin')->group(function () {
    // Rute login di luar middleware, agar bisa diakses
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'authenticate'])->name('admin.login.post');

    // Rute di dalam middleware, hanya bisa diakses setelah login
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
        // --- IGNORE ---
        // Rute untuk input data, pengelolaan capaian, dan materi
        Route::get('/input', [AdminController::class, 'showInputForm'])->name('admin.input');
        Route::post('/store-group', [StoreController::class, 'storeGroup'])->name('admin.store.group');
        Route::post('/store-stars', [StoreController::class, 'storeStars'])->name('admin.store.stars');
        // Rute untuk pengelolaan capaian
        Route::get('/achievements/input', [AdminController::class, 'showAchievementsInput'])->name('admin.achievements.input');
        Route::post('/achievements/store-criteria', [StoreController::class, 'storeAchievementCriteria'])->name('admin.achievements.store.criteria');
        Route::post('/achievements/update-status', [UpdateController::class, 'updateAchievementStatus'])->name('admin.achievements.update.status');
        // Rute untuk pengelolaan materi
        Route::get('/materials_assets', [AdminController::class, 'showMaterialsAssets'])->name('admin.materials.show');
        Route::post('/materials', [StoreController::class, 'storeMaterial'])->name('admin.materials.store');
        Route::delete('/materials/{material}', [DelateController::class, 'destroyMaterial'])->name('admin.materials.destroy');
        // Rute untuk pengelolaan tugas
        Route::get('/tasks', [AdminController::class, 'indexTasks'])->name('admin.task_manager.tasks');
        Route::get('/tasks/create-question', [MainController::class, 'createQuestionForm'])->name('admin.task_manager.create_question');
        // Rute untuk mengelola tugas
        Route::post('/tasks/store', [StoreController::class, 'storeTask'])->name('admin.tasks.store');
        Route::get('/tasks/manage-submission', [AdminController::class, 'manageSubmissions'])->name('admin.task_manager_submission');
        Route::post('/tasks/submissions/{submission}/score', [UpdateController::class, 'updateSubmissionScore'])->name('admin.tasks.update_score');
        Route::delete('/tasks/{task}', [DelateController::class, 'destroyTask'])->name('admin.tasks.destroy');
        Route::delete('/submissions/{submission}', [DelateController::class, 'destroySubmission'])->name('admin.submissions.destroy');
        Route::get('/tasks/{task}/edit', [AdminController::class, 'editTask'])->name('admin.tasks.edit');
        Route::put('/tasks/{task}', [UpdateController::class, 'updateTask'])->name('admin.tasks.update');
        // Rute baru untuk melihat jawaban siswa
        Route::get('/submissions/{submission}/answers', [AdminController::class, 'getSubmissionAnswers'])->name('admin.submissions.answers');
        // Rute untuk pengelolaan cerita
        Route::get('/stories/create', [StoryController::class, 'create'])->name('admin.stories.create');
        Route::post('/stories', [StoryController::class, 'store'])->name('admin.stories.store');
        Route::delete('/stories/{story}', [StoryController::class, 'destroy'])->name('admin.stories.destroy');
    });
});
