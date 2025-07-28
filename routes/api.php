<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Student;
use App\Models\Achievement;
use App\Models\StudentAchievement;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Contoh rute default Laravel Breeze/Jetstream, biarkan saja jika ada
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rute API untuk mendapatkan daftar capaian (kriteria dan status) untuk siswa tertentu
Route::get('/student-achievements/{student}', function (Student $student) {
    // Pastikan relasi 'group' dimuat pada model Student
    if (!$student->relationLoaded('group')) {
        $student->load('group');
    }

    $classGrade = $student->group->class_grade ?? null;

    if (!$classGrade) {
        // Jika kelas siswa tidak ditemukan, kembalikan respons 404
        return response()->json(['success' => false, 'message' => 'Kelas siswa tidak ditemukan.'], 404);
    }

    // Ambil semua kriteria capaian yang relevan dengan kelas siswa ini
    $allClassAchievements = Achievement::where('class_grade', $classGrade)->get();

    $achievementsData = [];
    foreach ($allClassAchievements as $achievement) {
        // Cari status capaian siswa untuk kriteria ini
        $studentAchievement = StudentAchievement::where('student_id', $student->id)
                                                ->where('achievement_id', $achievement->id)
                                                ->first();

        $isCompleted = $studentAchievement ? (bool) $studentAchievement->is_completed : false;
        $studentAchievementId = $studentAchievement ? $studentAchievement->id : null;

        $achievementsData[] = [
            'id' => $achievement->id, // ID kriteria capaian
            'description' => $achievement->description,
            'is_completed' => $isCompleted,
            'student_achievement_id' => $studentAchievementId, // ID dari tabel student_achievements (jika ada)
        ];
    }

    // Urutkan berdasarkan deskripsi untuk tampilan yang konsisten
    usort($achievementsData, function($a, $b) {
        return strcmp($a['description'], $b['description']);
    });

    return response()->json([
        'success' => true,
        'achievements' => $achievementsData
    ]);
});
