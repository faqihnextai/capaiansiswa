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
        $tasks = Task::with(['groups', 'students'])->orderBy('deadline', 'asc')->get();

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
        $groups = Group::with('students')->orderBy('name')->get();
        return view('admin.task_manager.create_question', compact('groups'));
    }

    /**
     * Menampilkan detail tugas untuk siswa dan form pengerjaan.
     */
    public function showStudentTaskDetail(Request $request, Task $task)
    {
        $task->load('questions');
        $studentId = $request->query('student_id') ?? session('siswa_id') ?? Student::first()->id;
        $student = Student::find($studentId);
        if (!$student) {
            return redirect()->route('public.tugas')->with('error', 'Siswa tidak ditemukan.');
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
