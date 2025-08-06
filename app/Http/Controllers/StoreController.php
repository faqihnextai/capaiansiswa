<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Student;
use App\Models\Achievement;
use App\Models\StudentAchievement;
use App\Models\Material;
use App\Models\Task;

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
}
