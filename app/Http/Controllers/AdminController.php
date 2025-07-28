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

        if (Auth::attempt($credentials)) {
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
     * Menangani penyimpanan data kelompok siswa dari form.
     */
    public function storeGroup(Request $request)
    {
        $request->validate([
            'group_name' => 'required|string|max:255',
            'class_grade' => 'required|in:4,5,6',
            'students' => 'required|array|min:1',
            'students.*.name' => 'required|string|max:255',
        ]);

        $group = Group::create([
            'name' => $request->group_name,
            'class_grade' => $request->class_grade,
        ]);

        foreach ($request->students as $studentData) {
            $group->students()->create([
                'name' => $studentData['name'],
                'stars' => null,
            ]);
        }

        $request->session()->flash('success_message_group', 'Data kelompok dan siswa berhasil disimpan!');
        return redirect()->route('admin.input', ['section' => 'group']);
    }

    /**
     * Menangani penyimpanan/pembaruan nilai bintang siswa individual.
     */
    public function storeStars(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'stars' => 'required|integer|min:1|max:5',
        ]);

        $student = Student::find($request->student_id);

        if ($student) {
            $student->stars = $request->stars;
            $student->save();
            $request->session()->flash('success_message_stars', 'Nilai bintang siswa ' . $student->name . ' berhasil diperbarui!');
        } else {
            $request->session()->flash('error_message_stars', 'Siswa tidak ditemukan.');
        }

        return redirect()->route('admin.input', ['section' => 'stars']);
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
     * Menyimpan kriteria capaian baru dan menginisialisasi StudentAchievement untuk siswa di kelas terkait.
     */
    public function storeAchievementCriteria(Request $request)
    {
        $request->validate([
            'class_grade' => 'required|in:4,5,6',
            'description' => 'required|string|max:255',
        ]);

        $achievement = Achievement::create([
            'class_grade' => $request->class_grade,
            'description' => $request->description,
        ]);

        $students = Student::whereHas('group', function ($q) use ($request) {
            $q->where('class_grade', $request->class_grade);
        })->get();

        foreach ($students as $student) {
            StudentAchievement::create([
                'student_id' => $student->id,
                'achievement_id' => $achievement->id,
                'is_completed' => false,
            ]);
        }

        return redirect()->route('admin.achievements.input')->with('success_message', 'Kriteria capaian berhasil ditambahkan!');
    }

    /**
     * Memperbarui status capaian siswa (AJAX).
     */
    public function updateAchievementStatus(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'achievement_id' => 'required|exists:achievements,id',
            'is_completed' => 'required|boolean',
        ]);

        $studentAchievement = StudentAchievement::firstOrCreate([
            'student_id' => $request->student_id,
            'achievement_id' => $request->achievement_id,
        ]);

        $studentAchievement->is_completed = $request->is_completed;
        $studentAchievement->save();

        return response()->json([
            'success' => true,
            'message' => 'Status capaian siswa berhasil diperbarui.',
        ]);
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
     * Menyimpan aset baru.
     */
    public function storeMaterial(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'class_grade' => 'required|in:4,5,6',
            'asset_type' => 'required|in:link,file,text',
            'file_asset' => 'nullable|file|mimes:ppt,pptx,pdf|max:10240', // Max 10MB
            'link_asset' => 'nullable|url',
            'text_asset' => 'nullable|string',
        ]);

        $content = null;
        if ($request->asset_type == 'file' && $request->hasFile('file_asset')) {
            $content = $request->file('file_asset')->store('materials', 'public');
        } elseif ($request->asset_type == 'link') {
            $content = $request->link_asset;
        } elseif ($request->asset_type == 'text') {
            $content = $request->text_asset;
        }

        Material::create([
            'title' => $request->title,
            'class_grade' => $request->class_grade,
            'asset_type' => $request->asset_type,
            'content' => $content,
        ]);

        return redirect()->route('admin.materials.show')->with('success', 'Aset berhasil diunggah!');
    }

    /**
     * Menghapus aset.
     */
    public function destroyMaterial(Material $material)
    {
        if ($material->asset_type == 'file') {
            Storage::disk('public')->delete($material->content);
        }
        $material->delete();

        return redirect()->route('admin.materials.show')->with('success', 'Aset berhasil dihapus!');
    }

    /**
     * Menampilkan halaman tugas dengan materi yang relevan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showTugas(Request $request)
    {
        // Ambil semua kelas yang ada dari tabel groups
        $classes = Group::select('class_grade')->distinct()->orderBy('class_grade')->pluck('class_grade');

        // Ambil semua kelompok dengan siswa-siswanya
        $groups = Group::with('students')->orderBy('name')->get();

        // Ambil semua siswa dengan kelompoknya
        $students = Student::with('group')->orderBy('name')->get();

        // Ambil semua materi dan tugas tanpa filter awal
        $materials = Material::orderBy('created_at', 'desc')->get();
        // Memuat tugas beserta relasi groups dan students
        $tasks = Task::with(['groups', 'students'])->orderBy('deadline', 'asc')->get();

        // Ambil kelas dan kelompok yang dipilih sebelumnya dari session atau query parameter
        $selectedClass = $request->session()->get('siswa_role') ?? $request->query('class');
        $selectedGroup = $request->session()->get('siswa_group') ?? $request->query('group');
        $selectedStudent = $request->session()->get('siswa_id') ?? $request->query('student');


        return view('tugas', compact('materials', 'tasks', 'classes', 'groups', 'students', 'selectedClass', 'selectedGroup', 'selectedStudent'));
    }

    /**
     * Menampilkan halaman pembuatan soal tugas untuk admin.
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
     * Menyimpan tugas dan soal-soal yang dibuat admin.
     */
    public function storeTask(Request $request)
    {
        // --- DEBUGGING START ---
        // Log::info('Request Data (storeTask):', $request->all());
        // --- DEBUGGING END ---

        // Validasi data tugas utama
        $request->validate([
            'task_title' => 'required|string|max:255',
            'class_grade' => 'required|in:4,5,6',
            'deadline_date' => 'required|date_format:Y-m-d',
            'deadline_time' => 'required|date_format:H:i',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:groups,id',
            'student_ids' => 'nullable|array', // Tambahkan validasi untuk student_ids
            'student_ids.*' => 'exists:students,id', // Pastikan ID siswa ada di tabel students
            'questions' => 'required|array|min:1',
            'questions.*.type' => 'required|in:multiple_choice,essay,true_false,matching,image_input',
            'questions.*.question_text' => 'required|string',
            'questions.*.score' => 'nullable|integer|min:0',
            // Validasi kondisional untuk tipe soal
            'questions.*.options.a' => 'required_if:questions.*.type,multiple_choice|string|max:255',
            'questions.*.options.b' => 'required_if:questions.*.type,multiple_choice|string|max:255',
            'questions.*.options.c' => 'required_if:questions.*.type,multiple_choice|string|max:255',
            'questions.*.options.d' => 'required_if:questions.*.type,multiple_choice|string|max:255',
            'questions.*.correct_answer' => 'required_if:questions.*.type,multiple_choice,true_false,essay|string|max:255',
            'questions.*.matching_pairs' => 'required_if:questions.*.type,matching|array|min:1',
            'questions.*.matching_pairs.*.left' => 'required|string|max:255',
            'questions.*.matching_pairs.*.right' => 'required|string|max:255',
            'questions.*.media' => 'nullable|file|image|max:5120', // Max 5MB untuk gambar
        ]);

        $deadline = $request->deadline_date . ' ' . $request->deadline_time;

        $task = Task::create([
            'title' => $request->task_title,
            'class_grade' => $request->class_grade,
            'deadline' => $deadline,
        ]);

        // 2. Kaitkan tugas dengan kelompok atau siswa
        if ($request->has('student_ids') && !empty($request->student_ids)) {
            $task->groups()->detach(); // Detach all groups if students are specifically chosen
            $task->students()->attach($request->student_ids);
        } elseif ($request->has('group_ids') && !empty($request->group_ids)) {
            $task->students()->detach(); // Detach all students if groups are chosen
            $task->groups()->attach($request->group_ids);
        } else {
            // Jika tidak ada kelompok atau siswa yang dipilih, kaitkan dengan semua kelompok di kelas yang sama
            $allGroupsInClass = Group::where('class_grade', $request->class_grade)->pluck('id');
            $task->students()->detach();
            $task->groups()->attach($allGroupsInClass);
        }


        // 3. Loop melalui setiap soal dan simpan
        foreach ($request->questions as $qId => $questionData) {
            $mediaPath = null;
            if ($request->hasFile("questions.{$qId}.media")) {
                $mediaFile = $request->file("questions.{$qId}.media");
                $mediaPath = $mediaFile->store('task_media', 'public');
            }

            $options = null;
            $correctAnswer = null;
            $score = (int) ($questionData['score'] ?? 0);

            switch ($questionData['type']) {
                case 'multiple_choice':
                    $options = json_encode([
                        'a' => $questionData['options']['a'],
                        'b' => $questionData['options']['b'],
                        'c' => $questionData['options']['c'],
                        'd' => $questionData['options']['d'],
                    ]);
                    $correctAnswer = json_encode($questionData['correct_answer']);
                    break;
                case 'essay':
                    $correctAnswer = isset($questionData['correct_answer']) ? json_encode($questionData['correct_answer']) : null;
                    break;
                case 'true_false':
                    $correctAnswer = json_encode($questionData['correct_answer']);
                    break;
                case 'matching':
                    $options = json_encode($questionData['matching_pairs']);
                    break;
                case 'image_input':
                    $options = isset($questionData['instructions']) ? json_encode($questionData['instructions']) : null;
                    break;
                default:
                    break;
            }

            $task->questions()->create([
                'type' => $questionData['type'],
                'content' => $questionData['question_text'], // Pastikan 'content' diisi dengan 'question_text'
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'score' => $score,
                'media_path' => $mediaPath,
            ]);
        }

        return redirect()->route('admin.tasks.index')->with('success', 'Tugas dan soal berhasil dibuat!');
    }

    /**
     * Menampilkan daftar tugas untuk dikelola admin.
     */
    public function indexTasks()
    {
        $tasks = Task::with(['groups', 'questions', 'submissions.student'])->orderBy('deadline', 'desc')->get();
        return view('admin.task_manager.index', compact('tasks'));
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
     * Memperbarui tugas yang sudah ada.
     */
    public function updateTask(Request $request, Task $task)
    {
        $request->validate([
            'task_title' => 'required|string|max:255',
            'class_grade' => 'required|in:4,5,6',
            'deadline_date' => 'required|date_format:Y-m-d',
            'deadline_time' => 'required|date_format:H:i',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:groups,id',
            'student_ids' => 'nullable|array', // Validasi untuk student_ids
            'student_ids.*' => 'exists:students,id', // Pastikan ID siswa ada di tabel students
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'nullable|exists:questions,id', // Untuk soal yang sudah ada
            'questions.*.type' => 'required|in:multiple_choice,essay,true_false,matching,image_input',
            'questions.*.question_text' => 'required|string',
            'questions.*.score' => 'nullable|integer|min:0',
            'questions.*.options.a' => 'required_if:questions.*.type,multiple_choice|string|max:255',
            'questions.*.options.b' => 'required_if:questions.*.type,multiple_choice|string|max:255',
            'questions.*.options.c' => 'required_if:questions.*.type,multiple_choice|string|max:255',
            'questions.*.options.d' => 'required_if:questions.*.type,multiple_choice|string|max:255',
            'questions.*.correct_answer' => 'required_if:questions.*.type,multiple_choice,true_false,essay|string|max:255',
            'questions.*.matching_pairs' => 'required_if:questions.*.type,matching|array|min:1',
            'questions.*.matching_pairs.*.left' => 'required|string|max:255',
            'questions.*.matching_pairs.*.right' => 'required|string|max:255',
            'questions.*.media' => 'nullable|file|image|max:5120', // Max 5MB untuk gambar
            'questions.*.existing_media_path' => 'nullable|string', // Untuk menyimpan path media yang sudah ada
            'deleted_questions' => 'nullable|array', // Array ID soal yang dihapus
            'deleted_questions.*' => 'exists:questions,id',
        ]);

        $deadline = $request->deadline_date . ' ' . $request->deadline_time;

        $task->update([
            'title' => $request->task_title,
            'class_grade' => $request->class_grade,
            'deadline' => $deadline,
        ]);

        // Sinkronkan relasi groups atau students
        if ($request->has('student_ids') && !empty($request->student_ids)) {
            $task->groups()->detach(); // Detach all groups if students are specifically chosen
            $task->students()->sync($request->student_ids);
        } elseif ($request->has('group_ids') && !empty($request->group_ids)) {
            $task->students()->detach(); // Detach all students if groups are chosen
            $task->groups()->sync($request->group_ids);
        } else {
            // Jika tidak ada yang dipilih, kaitkan dengan semua kelompok di kelas yang sama
            $allGroupsInClass = Group::where('class_grade', $request->class_grade)->pluck('id');
            $task->students()->detach();
            $task->groups()->sync($allGroupsInClass);
        }

        // Hapus soal yang ditandai untuk dihapus
        if ($request->has('deleted_questions')) {
            Question::whereIn('id', $request->deleted_questions)->delete();
        }

        // Perbarui atau buat soal
        foreach ($request->questions as $qId => $questionData) {
            $mediaPath = $questionData['existing_media_path'] ?? null; // Pertahankan media yang sudah ada
            // Jika ada file media baru diunggah, timpa yang lama
            if ($request->hasFile("questions.{$qId}.media")) {
                // Hapus file lama jika ada
                if ($mediaPath && Storage::disk('public')->exists($mediaPath)) {
                    Storage::disk('public')->delete($mediaPath);
                }
                $mediaFile = $request->file("questions.{$qId}.media");
                $mediaPath = $mediaFile->store('task_media', 'public');
            }

            $options = null;
            $correctAnswer = null;
            $score = (int) ($questionData['score'] ?? 0);

            switch ($questionData['type']) {
                case 'multiple_choice':
                    $options = json_encode([
                        'a' => $questionData['options']['a'],
                        'b' => $questionData['options']['b'],
                        'c' => $questionData['options']['c'],
                        'd' => $questionData['options']['d'],
                    ]);
                    $correctAnswer = json_encode($questionData['correct_answer']);
                    break;
                case 'essay':
                    $correctAnswer = isset($questionData['correct_answer']) ? json_encode($questionData['correct_answer']) : null;
                    break;
                case 'true_false':
                    $correctAnswer = json_encode($questionData['correct_answer']);
                    break;
                case 'matching':
                    $options = json_encode($questionData['matching_pairs']);
                    break;
                case 'image_input':
                    $options = isset($questionData['instructions']) ? json_encode($questionData['instructions']) : null;
                    break;
                default:
                    break;
            }

            $questionDataToSave = [
                'type' => $questionData['type'],
                'content' => $questionData['question_text'], // Ganti 'question_text' dengan 'content'
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'score' => $score,
                'media_path' => $mediaPath,
            ];

            if (isset($questionData['id'])) {
                // Perbarui soal yang sudah ada
                $task->questions()->where('id', $questionData['id'])->update($questionDataToSave);
            } else {
                // Buat soal baru
                $task->questions()->create($questionDataToSave);
            }
        }

        return redirect()->route('admin.tasks.index')->with('success', 'Tugas berhasil diperbarui!');
    }

    /**
     * Menghapus tugas.
     */
    public function destroyTask(Task $task)
    {
        // Hapus juga soal-soal terkait dan file media jika ada
        foreach ($task->questions as $question) {
            if ($question->media_path && Storage::disk('public')->exists($question->media_path)) {
                Storage::disk('public')->delete($question->media_path);
            }
            $question->delete();
        }
        $task->delete();
        return redirect()->route('admin.tasks.index')->with('success', 'Tugas berhasil dihapus!');
    }

    /**
     * Menampilkan detail tugas untuk siswa dan form pengerjaan.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\View\View
     */
    public function showStudentTaskDetail(Request $request, Task $task) // Tambahkan Request $request
    {
        $task->load('questions');

        // Ambil student_id dari parameter URL, atau dari session, atau default ke siswa pertama
        $studentId = $request->query('student_id') ?? session('siswa_id') ?? Student::first()->id;

        // Simpan student_id ke session agar tetap terpilih di halaman tugas berikutnya
        session(['siswa_id' => $studentId]);

        // Jika studentId tidak valid atau tidak ditemukan, arahkan kembali
        $student = Student::find($studentId);
        if (!$student) {
            return redirect()->route('public.tugas')->with('error', 'Siswa tidak ditemukan atau belum dipilih. Silakan pilih siswa Anda terlebih dahulu.');
        }

        $submission = Submission::where('task_id', $task->id)
            ->where('student_id', $studentId)
            ->first();

        // Jika belum ada studentId di session, arahkan ke halaman tugas untuk memilih kelas/siswa
        if (!$studentId) {
            return redirect()->route('public.tugas')->with('error', 'Silakan pilih kelas dan kelompok Anda terlebih dahulu.');
        }


        // Hitung nilai maksimum yang mungkin untuk tugas ini
        // Menggunakan sum() pada collection questions yang sudah di-load
        $maxPossibleScore = $task->questions->sum('score');

        // --- TAMBAHAN PENTING: Ambil dan kirim variabel $classes dan $groups ---
        // Ini diperlukan jika layout utama (layouts.app) atau partial lain
        // yang di-include membutuhkan variabel ini.
        $classes = Group::select('class_grade')->distinct()->orderBy('class_grade')->pluck('class_grade');
        $groups = Group::orderBy('name')->get();
        $students = Student::with('group')->orderBy('name')->get();


        // Mungkin juga dibutuhkan di layout/partial
        return view('student.student_task_detail', compact('task', 'submission', 'studentId', 'classes', 'groups', 'students', 'maxPossibleScore'));
    }

    /**
     * Menangani pengumpulan jawaban tugas oleh siswa.
     */
    public function submitTask(Request $request, Task $task)
    {
        try {
            $validatedData = $request->validate([
                'student_id' => 'required|exists:students,id',
                'answers' => 'required|array',
                // Pastikan validasi untuk 'answers.*.question_id' dan 'answers.*.student_answer'
                // sudah sesuai dengan struktur data yang kamu kirim dari frontend.
                // Contoh: jika kamu mengirim answers sebagai array asosiatif (question_id => answer_value)
                // maka validasi 'answers.*.question_id' mungkin tidak diperlukan atau perlu disesuaikan.
                // Untuk saat ini, asumsikan struktur yang kamu kirim adalah array of objects
                // seperti { question_id: X, student_answer: Y }
                'answers.*.question_id' => 'required|exists:questions,id',
                'answers.*.student_answer' => 'nullable', // Jawaban bisa null jika tidak diisi
                // Jika kamu mengizinkan unggahan file (misal untuk tipe image_input), tambahkan validasi di sini
                'answers.*' => 'nullable', // Ini penting agar Laravel tidak error jika ada field lain di 'answers'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Jika validasi gagal, kembalikan response JSON dengan error
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422); // Kode status 422 Unprocessable Entity
        }
        $studentId = $request->student_id;
        $studentAnswers = $request->answers;

        // Temukan atau buat submission baru
        $submission = Submission::firstOrNew([
            'task_id' => $task->id,
            'student_id' => $studentId,
        ]);

        $totalScore = 0;
        $answers = [];

        foreach ($studentAnswers as $submittedAnswer) {
            $question = Question::find($submittedAnswer['question_id']);
            if ($question) {
                $studentAnswer = $submittedAnswer['student_answer'];
                $questionScore = $question->score ?? 0;
                $isCorrect = false;

                // Simpan jawaban siswa untuk setiap soal
                $answers[$question->id] = [
                    'question_text' => $question->question_text,
                    'type' => $question->type,
                    'student_answer' => $studentAnswer,
                    'correct_answer' => json_decode($question->correct_answer, true),
                    'score' => 0, // Inisialisasi skor untuk jawaban ini
                    'is_correct' => false,
                ];

                if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
                    $correctAnswer = json_decode($question->correct_answer, true);
                    if ((string) $studentAnswer === (string) $correctAnswer) {
                        $isCorrect = true;
                    }
                } elseif ($question->type === 'essay' || $question->type === 'image_input') {
                    // Jawaban esai dan input gambar dinilai secara manual oleh admin,
                    // jadi tidak ada penilaian otomatis di sini.
                    // Jika ada kunci jawaban, bisa disimpan untuk referensi admin.
                    $isCorrect = false; // Set false secara default, admin yang akan menilai
                } elseif ($question->type === 'matching') {
                    $correctPairs = json_decode($question->options, true); // Ini adalah kunci jawaban pasangan
                    // Log::info('Correct Pairs:', $correctPairs);
                    // Log::info('Student Answer for Matching:', $studentAnswer);

                    if (is_array($studentAnswer) && count($studentAnswer) === count($correctPairs)) {
                        $allPairsCorrect = true;
                        foreach ($correctPairs as $index => $pair) {
                            // Bandingkan jawaban siswa dengan kunci jawaban untuk setiap pasangan
                            // Pastikan indeks ada dan nilai 'left' dan 'right' cocok
                            if (
                                !isset($studentAnswer[$index]) ||
                                (string) ($studentAnswer[$index]['left'] ?? '') !== (string) ($pair['left'] ?? '') ||
                                (string) ($studentAnswer[$index]['right'] ?? '') !== (string) ($pair['right'] ?? '')
                            ) {
                                $allPairsCorrect = false;
                                break;
                            }
                        }
                        $isCorrect = $allPairsCorrect;
                    }
                }

                if ($isCorrect) {
                    $totalScore += $questionScore;
                    $answers[$question->id]['score'] = $questionScore;
                    $answers[$question->id]['is_correct'] = true;
                }
            }
        }

        $submission->answers = json_encode($answers);
        $submission->is_completed = true;
        $submission->submitted_at = now();
        $submission->score = $totalScore; // Ini akan menyimpan total nilai yang dihitung otomatis
        $submission->save();

        return response()->json(['success' => true, 'message' => 'Tugas berhasil dikumpulkan!', 'score' => $totalScore]);
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
        // Tambahkan baris ini untuk mengambil semua tugas
        $tasks = Task::with(['groups', 'students'])->orderBy('deadline', 'asc')->get(); // Pastikan relasi groups dan questions juga dimuat
        return view('admin.task_manager.manage_submission', compact('submissions', 'tasks'));
    }
    /**
     * Menangani pembaruan nilai dan feedback tugas oleh admin.
     */
    public function updateSubmissionScore(Request $request, Submission $submission)
    {
        $request->validate([
            'score' => 'nullable|integer|min:0|max:100',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $submission->score = $request->score;
        $submission->feedback = $request->feedback;
        $submission->save();

        return response()->json([
            'success' => true,
            'score' => $submission->score,
            'feedback' => $submission->feedback,
            'message' => 'Nilai dan umpan balik berhasil disimpan!'
        ]);
    }


    /**
     * Menghapus pengumpulan tugas (submission) siswa.
     */
    public function destroySubmission(Submission $submission)
    {
        // Jika ada file terkait dengan submission (misal: untuk image_input), hapus di sini
        // Contoh: if ($submission->answers && isset(json_decode($submission->answers, true)['media_path'])) { ... }
        $submission->delete();
        return back()->with('success', 'Pengumpulan tugas berhasil dihapus!');
    }
}
