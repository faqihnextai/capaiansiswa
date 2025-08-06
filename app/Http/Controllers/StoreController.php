<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Student;
use App\Models\Achievement;
use App\Models\StudentAchievement;
use App\Models\Material;
use App\Models\Task;
use App\Models\Question;
use App\Models\Submission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // Import Storage facade

class StoreController extends Controller
{
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

    public function storeTask(Request $request)
    {
        // --- DEBUGGING START ---
        Log::info('Request Data (storeTask):', $request->all());
        // --- DEBUGGING END ---

        // Validasi data tugas utama
        $request->validate([
            'task_title' => 'required|string|max:255',
            'class_grade' => 'required|in:4,5,6',
            'deadline_date' => 'required|date_format:Y-m-d',
            'deadline_time' => 'required|date_format:H:i',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:groups,id',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
            'questions' => 'required|array|min:1',
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
            'questions.*.media' => 'nullable|file|image|max:5120',
        ]);

        $deadline = $request->deadline_date . ' ' . $request->deadline_time;

        $task = Task::create([
            'title' => $request->task_title,
            'class_grade' => $request->class_grade,
            'deadline' => $deadline,
        ]);

        // Logika pengaitan tugas dengan kelompok atau siswa
        // Inisialisasi array untuk menyimpan ID siswa yang akan dikaitkan
        $studentsToAttach = [];

        if ($request->has('student_ids') && !empty($request->student_ids)) {
            // Jika siswa spesifik dipilih, gunakan ID siswa tersebut
            $studentsToAttach = $request->student_ids;
            Log::info('Task attached to specific students:', ['student_ids' => $studentsToAttach]);
        } elseif ($request->has('group_ids') && !empty($request->group_ids)) {
            // Jika kelompok dipilih, ambil semua siswa dari kelompok tersebut
            $studentsToAttach = Student::whereIn('group_id', $request->group_ids)->pluck('id')->toArray();
            $task->groups()->attach($request->group_ids); // Juga kaitkan dengan kelompok yang dipilih
            Log::info('Task attached to students from selected groups:', ['group_ids' => $request->group_ids, 'student_ids' => $studentsToAttach]);
        } else {
            // Jika tidak ada student_ids maupun group_ids yang dipilih,
            // Anda bisa memilih untuk tidak mengaitkan dengan siapa-siapa,
            // atau mengaitkan dengan semua siswa di kelas yang sama.
            // Saat ini, saya akan membiarkannya tidak mengaitkan jika tidak ada pilihan eksplisit.
            // Jika Anda ingin mengaitkan dengan semua siswa di kelas, aktifkan kembali kode di bawah:
            /*
            $allStudentsInClass = Student::whereHas('group', function ($query) use ($request) {
                $query->where('class_grade', $request->class_grade);
            })->pluck('id')->toArray();
            $studentsToAttach = $allStudentsInClass;
            Log::info('Task attached to all students in class (default):', ['class_grade' => $request->class_grade, 'student_ids' => $studentsToAttach]);
            */
            Log::info('No specific students or groups selected. Task will not be attached to any students by default.');
        }

        // Lakukan attach hanya jika ada siswa yang akan dikaitkan
        if (!empty($studentsToAttach)) {
            $task->students()->attach($studentsToAttach);
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
                'content' => $questionData['question_text'],
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'score' => $score,
                'media_path' => $mediaPath,
            ]);
        }

        return redirect()->route('admin.task_manager.tasks')->with('success', 'Tugas dan soal berhasil dibuat!');
    }

    /**
     * Menangani pengumpulan jawaban tugas oleh siswa.
     */
    public function submitTask(Request $request, Task $task)
    {
        // Validasi umum untuk submission
        $validationRules = [
            'student_id' => 'required|exists:students,id',
            'answers' => 'required|array',
            // 'answers.*.question_id' => 'required|exists:questions,id', // Ini akan dihandle di loop
        ];

        // Tambahkan validasi spesifik berdasarkan tipe soal
        foreach ($request->input('answers') as $index => $answer) {
            $questionId = $answer['question_id'] ?? null;
            $question = Question::find($questionId);

            if ($question) {
                $validationRules["answers.{$index}.question_id"] = 'required|exists:questions,id'; // Pastikan question_id ada dan valid

                if ($question->type === 'image_input') {
                    $validationRules["answers.{$index}.student_answer_file"] = 'nullable|file|image|max:5120'; // Untuk file baru
                    $validationRules["answers.{$index}.student_answer_existing_path"] = 'nullable|string'; // Untuk path yang sudah ada
                } elseif ($question->type === 'matching') {
                    // Validasi untuk soal matching (mengharapkan array of objects)
                    $validationRules["answers.{$index}.student_answer"] = 'nullable|array';
                    $validationRules["answers.{$index}.student_answer.*.left"] = 'required|string|max:255';
                    $validationRules["answers.{$index}.student_answer.*.right"] = 'required|string|max:255';
                } else {
                    // Untuk tipe soal lain (multiple_choice, essay, true_false)
                    $validationRules["answers.{$index}.student_answer"] = 'nullable|string';
                }
            }
        }

        try {
            $validatedData = $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error in submitTask:', ['errors' => $e->errors(), 'request' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        }

        $studentId = $request->student_id;
        $studentAnswers = $request->answers;

        // Pastikan siswa yang mengumpulkan tugas memang berhak mengerjakan tugas ini
        $student = Student::find($studentId);
        $isTaskForStudent = false;
        if ($task->students->isEmpty() && $task->groups->isEmpty()) {
            $studentGroup = $student->group;
            if ($studentGroup && $task->class_grade == $studentGroup->class_grade) {
                $isTaskForStudent = true;
            }
        } elseif ($task->students->isNotEmpty()) {
            if ($task->students->contains($studentId)) {
                $isTaskForStudent = true;
            }
        } elseif ($task->groups->isNotEmpty()) {
            if ($task->groups->contains($student->group_id)) {
                $isTaskForStudent = true;
            }
        }

        if (!$isTaskForStudent) {
            Log::warning('Unauthorized submission attempt:', ['student_id' => $studentId, 'task_id' => $task->id]);
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengerjakan tugas ini.'
            ], 403); // Forbidden
        }

        $submission = Submission::firstOrNew([
            'task_id' => $task->id,
            'student_id' => $studentId,
        ]);

        $totalScore = 0;
        $answers = [];

        foreach ($studentAnswers as $submittedAnswer) {
            $question = Question::find($submittedAnswer['question_id']);
            if ($question) {
                $currentStudentAnswer = null; // Inisialisasi untuk setiap iterasi

                if ($question->type === 'image_input') {
                    // Tangani upload file gambar
                    if ($request->hasFile("answers.{$submittedAnswer['question_id']}.student_answer_file")) {
                        $imageFile = $request->file("answers.{$submittedAnswer['question_id']}.student_answer_file");
                        $currentStudentAnswer = $imageFile->store('submission_images', 'public'); // Simpan gambar
                        Log::info('Image uploaded:', ['path' => $currentStudentAnswer]);
                    } elseif (isset($submittedAnswer['student_answer_existing_path'])) {
                        // Jika tidak ada file baru diunggah, gunakan path gambar yang sudah ada (jika ada)
                        $currentStudentAnswer = $submittedAnswer['student_answer_existing_path'];
                    }
                } else {
                    // Untuk tipe soal lain, ambil dari input biasa
                    $currentStudentAnswer = $submittedAnswer['student_answer'] ?? null;
                }

                $questionScore = $question->score ?? 0;
                $isCorrect = false;

                $answers[$question->id] = [
                    'question_text' => $question->content,
                    'type' => $question->type,
                    'student_answer' => $currentStudentAnswer, // Gunakan jawaban yang sudah diproses
                    'correct_answer' => json_decode($question->correct_answer, true),
                    'score' => 0,
                    'is_correct' => false,
                ];

                if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
                    $correctAnswer = json_decode($question->correct_answer, true);
                    if ((string) $currentStudentAnswer === (string) $correctAnswer) {
                        $isCorrect = true;
                    }
                } elseif ($question->type === 'essay' || $question->type === 'image_input') {
                    // Untuk esai dan image_input, nilai tidak otomatis di sini.
                    // Penilaian manual oleh admin.
                    $isCorrect = false;
                } elseif ($question->type === 'matching') {
                    $correctPairs = json_decode($question->options, true);
                    if (is_array($currentStudentAnswer) && count($currentStudentAnswer) === count($correctPairs)) {
                        $allPairsCorrect = true;
                        foreach ($correctPairs as $index => $pair) {
                            // Pastikan indeks ada dan nilai 'left' dan 'right' cocok
                            if (
                                !isset($currentStudentAnswer[$index]) ||
                                (string) ($currentStudentAnswer[$index]['left'] ?? '') !== (string) ($pair['left'] ?? '') ||
                                (string) ($currentStudentAnswer[$index]['right'] ?? '') !== (string) ($pair['right'] ?? '')
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
        $submission->score = $totalScore;
        $submission->save();

        return response()->json(['success' => true, 'message' => 'Tugas berhasil dikumpulkan!', 'score' => $totalScore]);
    }
}
