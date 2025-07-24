<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Models\Achievement;
use App\Models\StudentAchievement;
use App\Models\Material;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// --- Rute untuk Sisi Umum (Publik) ---
Route::get('/', [AdminController::class, 'publicDashboard'])->name('public.dashboard');
Route::get('/diskusi', [AdminController::class, 'welcome'])->name('public.diskusi');
Route::get('/capaian', [AdminController::class, 'capaian'])->name('public.capaian');
// Route untuk halaman materi pembelajaran untuk publik (opsional dengan filter kelas)
Route::get('/materi/{class?}', [AdminController::class, 'showPublicMaterials'])->name('public.materi');
Route::get('/tugas', [AdminController::class, 'showTugas'])->name('public.tugas');

// Route untuk halaman detail tugas siswa (tempat siswa mengerjakan soal)
Route::get('/tugas/{task}', [AdminController::class, 'showStudentTaskDetail'])->name('student.task.show');
Route::post('/tugas/{task}/submit', [AdminController::class, 'submitTask'])->name('student.task.submit');

// --- Rute untuk Sisi Admin ---
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'authenticate'])->name('admin.login.post');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/input', [AdminController::class, 'showInputForm'])->name('admin.input');
    Route::post('/store-group', [AdminController::class, 'storeGroup'])->name('admin.store.group');
    Route::post('/store-stars', [AdminController::class, 'storeStars'])->name('admin.store.stars');

    Route::get('/achievements/input', [AdminController::class, 'showAchievementsInput'])->name('admin.achievements.input');
    Route::post('/achievements/store-criteria', [AdminController::class, 'storeAchievementCriteria'])->name('admin.achievements.store.criteria');
    Route::post('/achievements/update-status', [AdminController::class, 'updateAchievementStatus'])->name('admin.achievements.update.status');

    Route::get('/materials_assets', [AdminController::class, 'showMaterialsAssets'])->name('admin.materials.show');
    Route::post('/materials', [AdminController::class, 'storeMaterial'])->name('admin.materials.store');
    Route::delete('/materials/{material}', [AdminController::class, 'destroyMaterial'])->name('admin.materials.destroy');

    // --- Rute untuk Kelola Tugas & Soal ---
    Route::get('/tasks', function () {
        return view('admin.tasks');
    })->name('admin.tasks.index');

    // Rute untuk menghapus tugas
    Route::delete('/tasks/{task}', [AdminController::class, 'destroyTask'])->name('admin.tasks.destroy');

    // Rute untuk menghapus submission (pengumpulan tugas) siswa
    Route::delete('/submissions/{submission}', [AdminController::class, 'destroySubmission'])->name('admin.submissions.destroy');

    Route::get('/tasks/create-question', [AdminController::class, 'createQuestionForm'])->name('admin.task_manager.create_question');
    Route::post('/tasks/store', [AdminController::class, 'storeTask'])->name('admin.tasks.store');

    // Rute untuk mengelola dan mengecek tugas siswa (menampilkan submissions)
    Route::get('/tasks/manage-submission', [AdminController::class, 'manageSubmissions'])->name('admin.task_manager_submission');

    // Rute untuk mengupdate nilai tugas (AJAX)
    Route::post('/tasks/submissions/{submission}/score', [AdminController::class, 'updateSubmissionScore'])->name('admin.tasks.update_score');
});

// Catatan: Middleware 'admin' akan ditambahkan nanti untuk melindungi rute dashboard.
// Untuk saat ini, kita fokus memastikan rute dasar berfungsi tanpa error.
