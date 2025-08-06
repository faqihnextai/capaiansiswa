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
        return view('admin.task_manager.tasks', compact('tasks', 'groups')); // UBAH INI DARI 'index' MENJADI 'tasks'
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

    /**
     * Menampilkan halaman pengelolaan pengumpulan tugas untuk admin.
     */
    public function manageSubmissions(Request $request)
    {
        // Ambil semua tugas dengan relasi yang diperlukan
        $tasks = Task::with(['groups', 'students'])->orderBy('deadline', 'asc')->get();

        // Mulai query untuk submissions
        $submissionsQuery = Submission::with(['task', 'student'])
            ->orderBy('submitted_at', 'desc');

        // Filter berdasarkan task_id jika ada di request
        if ($request->has('task_id') && !empty($request->task_id)) {
            $submissionsQuery->where('task_id', $request->task_id);
        }

        // Filter berdasarkan student_id jika ada di request
        if ($request->has('student_id') && !empty($request->student_id)) {
            $submissionsQuery->where('student_id', $request->student_id);
        }

        $submissions = $submissionsQuery->get();

        // Filter tasks yang akan ditampilkan di dropdown berdasarkan submissions yang ada
        // Ini memastikan dropdown filter tugas hanya menampilkan tugas yang memiliki setidaknya satu submission
        $taskIdsWithSubmissions = $submissions->pluck('task_id')->unique()->toArray();
        $filteredTasksForDropdown = $tasks->filter(function ($task) use ($taskIdsWithSubmissions) {
            return in_array($task->id, $taskIdsWithSubmissions);
        });


        // Ambil semua siswa untuk dropdown filter
        $allStudents = Student::orderBy('name')->get();

        return view('admin.task_manager.manage_submission', compact('submissions', 'tasks', 'allStudents'));
    }
     public function getSubmissionAnswers(Submission $submission)
    {
        // Load relasi task dan questions dari task untuk mendapatkan detail soal
        $submission->load('task.questions');

        $answersData = [];
        // Decode kolom 'answers' dari submission
        $submittedAnswers = json_decode($submission->answers, true);

        if ($submittedAnswers) {
            foreach ($submittedAnswers as $questionId => $answer) {
                $question = $submission->task->questions->find($questionId);
                if ($question) {
                    $answersData[] = [
                        'question_text' => $question->content,
                        'question_type' => $question->type,
                        'question_options' => json_decode($question->options, true), // Decode options jika ada
                        'correct_answer' => json_decode($question->correct_answer, true), // Decode correct_answer
                        'student_answer' => $answer['student_answer'] ?? null,
                        'is_correct' => $answer['is_correct'] ?? false,
                        'score' => $answer['score'] ?? 0,
                        'media_path' => $question->media_path, // Path media soal jika ada
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'student_name' => $submission->student->name,
            'task_title' => $submission->task->title,
            'answers' => $answersData,
        ]);
    }
}
