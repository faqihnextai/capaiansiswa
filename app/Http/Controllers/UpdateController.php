<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\StudentAchievement;
use App\Models\Task;
use App\Models\Question;
use App\Models\Submission;

class UpdateController extends Controller
{
    /* ========== UPDATE ACHIEVEMENT STATUS ========== */

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

    /* ========== UPDATE TASK ========== */

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
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'nullable|exists:questions,id',
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
            'questions.*.existing_media_path' => 'nullable|string',
            'deleted_questions' => 'nullable|array',
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
            $task->groups()->detach();
            $task->students()->sync($request->student_ids);
        } elseif ($request->has('group_ids') && !empty($request->group_ids)) {
            $task->students()->detach();
            $task->groups()->sync($request->group_ids);
        } else {
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
            $mediaPath = $questionData['existing_media_path'] ?? null;
            if ($request->hasFile("questions.{$qId}.media")) {
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
                'content' => $questionData['question_text'],
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'score' => $score,
                'media_path' => $mediaPath,
            ];

            if (isset($questionData['id'])) {
                $task->questions()->where('id', $questionData['id'])->update($questionDataToSave);
            } else {
                $task->questions()->create($questionDataToSave);
            }
        }

        return redirect()->route('admin.tasks.index')->with('success', 'Tugas berhasil diperbarui!');
    }

    /* ========== UPDATE SUBMISSION SCORE ========== */

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
}
