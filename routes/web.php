<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Models\Achievement;
use App\Models\StudentAchievement;
use App\Models\Material; // Pastikan ini diimpor

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
// Route untuk halaman utama (Dashboard)
Route::get('/', [AdminController::class, 'publicDashboard'])->name('public.dashboard');

// Route untuk halaman Diskusi (sebelumnya welcome)
Route::get('/diskusi', [AdminController::class, 'welcome'])->name('public.diskusi');

// Route untuk halaman Capaian Siswa
Route::get('/capaian', [App\Http\Controllers\AdminController::class, 'capaian'])->name('public.capaian');

// Route untuk halaman Materi Pembelajaran
Route::get('/materi', [AdminController::class, 'showPublicMaterials'])->name('public.materi');

// Route untuk halaman Tugas
Route::get('/tugas', function () {
    return view('tugas');
})->name('public.tugas');


// --- Rute untuk Sisi Admin ---
Route::prefix('admin')->group(function () {
    // Halaman login admin (GET request untuk menampilkan form)
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');

    // Proses autentikasi (login) admin (POST request dari form)
    Route::post('/login', [AdminController::class, 'authenticate'])->name('admin.login.post');

    // Halaman dashboard admin (hanya bisa diakses setelah login)
    // Route ini tetap ada untuk akses admin setelah login, terpisah dari root URL
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Proses logout admin (POST request)
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

    // Halaman input data
    Route::get('/input', [AdminController::class, 'showInputForm'])->name('admin.input');

    // Proses penyimpanan data input
    Route::post('/input', [AdminController::class, 'storeInput'])->name('admin.input.store');

    // Proses penyimpanan data grup
    Route::post('/store-group', [AdminController::class, 'storeGroup'])->name('admin.store.group');

    // Proses penyimpanan data bintang
    Route::post('/store-stars', [AdminController::class, 'storeStars'])->name('admin.store.stars');

    // Halaman input capaian siswa
    Route::get('/achievements/input', [AdminController::class, 'showAchievementsInput'])->name('admin.achievements.input');

    // Proses penyimpanan kriteria capaian baru
    Route::post('/achievements/store-criteria', [AdminController::class, 'storeAchievementCriteria'])->name('admin.achievements.store.criteria');

    // Proses pembaruan status ceklis capaian siswa
    Route::post('/achievements/update-status', [AdminController::class, 'updateAchievementStatus'])->name('admin.achievements.update.status');
});

// Catatan: Middleware 'admin' akan ditambahkan nanti untuk melindungi rute dashboard.
// Untuk saat ini, kita fokus memastikan rute dasar berfungsi tanpa error.

Route::prefix('admin')->group(function () {
    Route::get('/materials_assets', [App\Http\Controllers\AdminController::class, 'showMaterialsAssets'])->name('admin.materials.show');
    Route::post('/materials', [App\Http\Controllers\AdminController::class, 'storeMaterial'])->name('admin.materials.store');
    Route::delete('/materials/{material}', [App\Http\Controllers\AdminController::class, 'destroyMaterial'])->name('admin.materials.destroy');
});
