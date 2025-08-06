<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Group;
use App\Models\Student;
use App\Models\Achievement;
use App\Models\StudentAchievement;
use App\Models\Material;
use App\Models\Task;
use App\Models\Question;
use App\Models\Submission;
use Illuminate\Support\Facades\Log; // Tambahkan ini untuk debugging

class MainController extends Controller
{
    /**
     * Menampilkan halaman utama (Menu) dengan data kelompok siswa.
     */
    public function welcome()
    {
        $groups = Group::with('students')->get();
        return view('welcome', compact('groups'));
    }

    /**
     * Menampilkan halaman capaian siswa dengan data relasional dari database.
     */
    public function capaian()
    {
        $classes = Group::select('class_grade')->distinct()->orderBy('class_grade')->pluck('class_grade');
        $groups = Group::with('students')->orderBy('name')->get();
        $students = Student::with('group')->orderBy('name')->get();

        $studentAchievementsData = Student::with(['studentAchievements.achievement', 'group'])->get()->mapWithKeys(function ($student) {
            $achievements = $student->studentAchievements->map(function ($sa) {
                return [
                    'id' => $sa->achievement->id,
                    'kriteria' => $sa->achievement->description,
                    'status' => (bool) $sa->is_completed,
                    'student_achievement_id' => $sa->id,
                ];
            })->toArray();
            return [$student->id => $achievements];
        })->toArray();

        $dummyAchievements = $studentAchievementsData;

        return view('capaian', compact('classes', 'groups', 'students', 'dummyAchievements'));
    }

    /**
     * Menampilkan halaman dashboard publik.
     */
    public function publicDashboard()
    {
        $classes = Group::select('class_grade')->distinct()->orderBy('class_grade')->pluck('class_grade');
        $groups = Group::with('students')->orderBy('name')->get();
        $students = Student::with('group')->orderBy('name')->get();

        $studentAchievementsData = Student::with(['studentAchievements.achievement', 'group'])->get()->mapWithKeys(function ($student) {
            $achievements = $student->studentAchievements->map(function ($sa) {
                return [
                    'id' => $sa->achievement->id,
                    'kriteria' => $sa->achievement->description,
                    'status' => (bool) $sa->is_completed,
                    'student_achievement_id' => $sa->id,
                ];
            })->toArray();
            return [$student->id => $achievements];
        })->toArray();

        $dummyAchievements = $studentAchievementsData;

        return view('public_dashboard', compact('classes', 'groups', 'students', 'dummyAchievements'));
    }

    /**
     * Menampilkan halaman materi pembelajaran untuk publik.
     */
    public function showPublicMaterials(Request $request, $class = null)
    {
        $classGrade = $class;
        if ($classGrade) {
            $materials = Material::where('class_grade', $classGrade)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $materials = Material::orderBy('created_at', 'desc')->get();
        }
        return view('materi', compact('materials', 'classGrade'));
    }

    /**
     * Menampilkan halaman tugas dengan materi yang relevan.
     */
    public function showTugas(Request $request)
    {
        $classes = Group::select('class_grade')->distinct()->orderBy('class_grade')->pluck('class_grade');
        $groups = Group::with('students')->orderBy('name')->get();
        $students = Student::with('group')->orderBy('name')->get();
        $materials = Material::orderBy('created_at', 'desc')->get();

        // Ambil ID siswa yang sedang dipilih dari session atau query parameter
        $selectedStudentId = $request->session()->get('siswa_id') ?? $request->query('student');

        // Query tugas:
        // 1. Ambil semua tugas dengan relasi groups dan students
        $tasksQuery = Task::with(['groups', 'students'])->orderBy('deadline', 'asc');

        // 2. Filter tugas berdasarkan siswa yang dipilih (jika ada)
        if ($selectedStudentId) {
            $tasksQuery->whereHas('students', function ($query) use ($selectedStudentId) {
                $query->where('students.id', $selectedStudentId);
            });
        }

        $tasks = $tasksQuery->get();

        $selectedClass = $request->session()->get('siswa_role') ?? $request->query('class');
        $selectedGroup = $request->session()->get('siswa_group') ?? $request->query('group');
        // $selectedStudent sudah diambil di atas

        return view('tugas', compact('materials', 'tasks', 'classes', 'groups', 'students', 'selectedClass', 'selectedGroup', 'selectedStudentId')); // Ubah $selectedStudent menjadi $selectedStudentId
    }

    /**
     * Menampilkan halaman pembuatan soal tugas untuk admin.
     */
    public function createQuestionForm()
    {
        $groups = Group::with('students')->orderBy('name')->get();
        return view('admin.task_manager.create_question', compact('groups'));
    }

    /**
     * Menampilkan detail tugas untuk siswa dan form pengerjaan.
     */
    public function showStudentTaskDetail(Request $request, Task $task)
    {
        $task->load('questions', 'students'); // Load relasi students pada task

        // Ambil ID siswa yang sedang login/dipilih dari session atau query parameter
        $studentId = $request->query('student_id') ?? session('siswa_id');

        // Jika tidak ada studentId yang ditemukan, arahkan kembali dengan error
        if (!$studentId) {
            return redirect()->route('public.tugas')->with('error', 'Siswa tidak teridentifikasi. Silakan pilih siswa terlebih dahulu.');
        }

        $student = Student::find($studentId);

        // Validasi apakah siswa yang sedang mengakses berhak mengerjakan tugas ini
        // Cek apakah tugas ini ditujukan untuk semua siswa di kelas tertentu, atau siswa spesifik
        $isTaskForStudent = false;
        if ($task->students->isEmpty()) {
            // Jika task tidak punya relasi siswa spesifik, berarti untuk semua siswa di kelas tersebut
            $studentGroup = $student->group;
            if ($studentGroup && $task->class_grade == $studentGroup->class_grade) {
                $isTaskForStudent = true;
            }
        } else {
            // Jika task punya relasi siswa spesifik, cek apakah siswa ini termasuk
            if ($task->students->contains($studentId)) {
                $isTaskForStudent = true;
            }
        }

        if (!$isTaskForStudent) {
            return redirect()->route('public.tugas')->with('error', 'Anda tidak memiliki izin untuk mengerjakan tugas ini.');
        }

        $submission = Submission::where('task_id', $task->id)
            ->where('student_id', $studentId)
            ->first();

        $classes = Group::select('class_grade')->distinct()->orderBy('class_grade')->pluck('class_grade');
        $groups = Group::orderBy('name')->get();
        $students = Student::with('group')->orderBy('name')->get();

        return view('student.student_task_detail', compact('task', 'submission', 'studentId', 'classes', 'groups', 'students'));
    }
}
