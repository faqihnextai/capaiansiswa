<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Material;
use App\Models\Task;
use App\Models\Submission;

class DelateController extends Controller
{
    /* ========== DELETE MATERIALS ========== */

    /**
     * Menghapus aset/materi.
     */
    public function destroyMaterial(Material $material)
    {
        if ($material->asset_type == 'file') {
            Storage::disk('public')->delete($material->content);
        }
        $material->delete();

        return redirect()->route('admin.materials.show')->with('success', 'Aset berhasil dihapus!');
    }

    /* ========== DELETE TASKS ========== */

    /**
     * Menghapus tugas beserta soal dan file terkait.
     */
    public function destroyTask(Task $task)
    {
        // Hapus file media soal terkait
        foreach ($task->questions as $question) {
            if ($question->media_path && Storage::disk('public')->exists($question->media_path)) {
                Storage::disk('public')->delete($question->media_path);
            }
            // Hapus soal
            $question->delete();
        }

        // Hapus relasi tugas dengan kelompok (jika ada)
        $task->groups()->detach();

        // Hapus relasi tugas dengan siswa (jika ada)
        $task->students()->detach();

        // Hapus semua submission terkait dengan tugas ini
        // Ini akan menghapus entri di tabel 'submissions' yang terkait dengan task_id ini
        Submission::where('task_id', $task->id)->delete();


        // Hapus tugas itu sendiri
        $task->delete();

        // Redirect ke halaman manage_submission dengan pesan sukses
        return redirect()->route('admin.task_manager_submission')->with('success', 'Tugas berhasil dihapus!');
    }

    /* ========== DELETE SUBMISSIONS ========== */

    /**
     * Menghapus pengumpulan tugas (submission) siswa.
     */
    public function destroySubmission(Submission $submission)
    {
        // Hapus file terkait jika ada
        if ($submission->answers) {
            $answers = json_decode($submission->answers, true);
            // Tambahkan logika penghapusan file jika ada media_path di jawaban
        }

        $submission->delete();
        return back()->with('success', 'Pengumpulan tugas berhasil dihapus!');
    }
}
