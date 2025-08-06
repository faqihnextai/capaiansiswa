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
use App\Models\Submission; // Import model Submission
use Illuminate\Validation\Rule; // Import Rule untuk validasi
use Illuminate\Support\Facades\Log; // Import Log facade

class AdminController extends Controller
{

    /**
     * Menampilkan form login admin.
     */
    public function showLoginForm()
    {
        return view('admin.login');
    }

    /**
     * Menangani proses autentikasi (login) admin.
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Gunakan guard 'admin' secara eksplisit
        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }


    /**
     * Menampilkan dashboard admin.
     */
    public function dashboard()
    {
        return view('admin.dashboard');
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
     * Menampilkan halaman form input data admin.
     * Mengirimkan daftar siswa, kelas, dan kelompok untuk dropdown di tab 'stars'.
     */
    public function showInputForm(Request $request)
    {
        $section = $request->query('section', 'group');
        $students = Student::with('group')->orderBy('name')->get();
        $classes = Group::select('class_grade')->distinct()->orderBy('class_grade')->pluck('class_grade');
        $groups = Group::with('students')->orderBy('name')->get();
        return view('admin.input', compact('section', 'students', 'classes', 'groups'));
    }


    /**
     * Menampilkan form input capaian siswa untuk admin.
     */
    public function showAchievementsInput()
    {
        $classes = Group::select('class_grade')->distinct()->orderBy('class_grade')->pluck('class_grade');
        $groups = Group::with('students')->orderBy('name')->get();
        $students = Student::with('group')->orderBy('name')->get();
        $achievements = Achievement::orderBy('created_at', 'desc')->get();

        return view('admin.achievements', compact('classes', 'groups', 'students', 'achievements'));
    }


    /**
     * Menampilkan halaman kelola materi dan aset.
     */
    public function showMaterialsAssets()
    {
        $materials = Material::orderBy('created_at', 'desc')->get();
        return view('admin.materials_assets', compact('materials'));
    }

    /**
     * Menampilkan halaman materi pembelajaran untuk publik.
     */public function showPublicMaterials(Request $request, $class = null) // Tambahkan $class sebagai parameter
    {
        $classGrade = $class; // Gunakan parameter $class dari route

        // --- DEBUGGING START ---
        \Log::info('AdminController@showPublicMaterials: classGrade received = ' . ($classGrade ?? 'NULL'));
        // --- DEBUGGING END ---
        if ($classGrade) {
            $materials = Material::where('class_grade', $classGrade)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Jika tidak ada kelas yang dipilih, tampilkan semua atau kosongkan (sesuai kebutuhan)
            // Untuk saat ini, kita akan tampilkan semua jika tidak ada filter kelas
            $materials = Material::orderBy('created_at', 'desc')->get();
        }

        return view('materi', compact('materials', 'classGrade')); // Kirimkan juga classGrade ke view
    }

    /**
     * Menampilkan halaman tugas dengan materi yang relevan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function createQuestionForm()
    {
        // Mengambil semua kelompok dengan siswa-siswanya untuk dropdown filter
        // Ini penting agar JavaScript bisa memfilter siswa berdasarkan kelompok
        $groups = Group::with('students')->orderBy('name')->get();
        // Jika kamu memiliki model Student terpisah dan ingin mengirimkan semua siswa juga:
        // $students = Student::orderBy('name')->get(); // Opsional, jika allGroups sudah memuat students
        return view('admin.task_manager.create_question', compact('groups')); // Tambahkan 'students' jika dikirim
    }


   /**
     * Menampilkan daftar tugas untuk admin.
     */
    public function indexTasks()
    {
        // Ambil semua tugas dengan relasi yang diperlukan
        $tasks = Task::with(['groups', 'questions', 'submissions.student'])->orderBy('deadline', 'desc')->get();
        // **PERBAIKAN DILAKUKAN DI SINI**
        // Ambil juga data groups untuk dropdown atau keperluan lain
        $groups = Group::with('students')->orderBy('name')->get();
        // Kirimkan variabel tasks dan groups ke view
        return view('admin.task_manager.index', compact('tasks', 'groups'));
    }

    /**
     * Menampilkan form untuk mengedit tugas.
     */
    public function editTask(Task $task)
    {
        $task->load('questions', 'groups', 'students'); // Load questions, groups, and students relationships
        $groups = Group::with('students')->orderBy('name')->get(); // Fetch all groups with students for the dropdowns
        return view('admin.task_manager.edit', compact('task', 'groups'));
    }

    /**
     * Menangani proses logout admin.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function manageSubmissions()
    {
        $submissions = Submission::with(['task', 'student'])->orderBy('submitted_at', 'desc')->get();
        $tasks = Task::with(['groups', 'students'])->orderBy('deadline', 'asc')->get();
        return view('admin.task_manager.manage_submission', compact('submissions', 'tasks'));
    }
}
