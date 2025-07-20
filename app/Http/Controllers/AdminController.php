<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; // Tambahkan ini untuk manajemen file
use App\Models\User;
use App\Models\Group;
use App\Models\Student;
use App\Models\Achievement;
use App\Models\StudentAchievement;
use App\Models\Material; // Import model Material

class AdminController extends Controller
{
    /**
     * Menampilkan halaman utama (Menu) dengan data kelompok siswa.
     */
    public function welcome()
    {
        $groups = Group::with('students')->get(); // Ambil semua kelompok beserta siswa dan nilai bintangnya
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

        // Ambil data capaian siswa dari database
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

        // Variabel ini yang harusnya dikirim ke view
        $dummyAchievements = $studentAchievementsData;

        // Pastikan 'dummyAchievements' ada di sini
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
        return view('public_dashboard');
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

        // Ambil semua siswa di kelas terkait
        $students = Student::whereHas('group', function($q) use ($request) {
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
     */
    public function showPublicMaterials()
    {
        $materials = Material::orderBy('created_at', 'desc')->get();
        return view('materi', compact('materials'));
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
            $content = $request->file('file_asset')->store('materials', 'public'); // Simpan di storage/app/public/materials
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
            // Hapus file dari storage jika ada
            Storage::disk('public')->delete($material->content);
        }
        $material->delete();

        return redirect()->route('admin.materials.show')->with('success', 'Aset berhasil dihapus!');
    }

    /**
     * Menampilkan halaman tugas dengan materi yang relevan.
     */
    public function showTugas()
    {
        // Untuk demo, kita asumsikan siswa kelas 4.
        // Di aplikasi nyata, Anda akan mendapatkan kelas siswa dari sesi atau autentikasi.
        $studentClass = 4; // Contoh: Asumsi siswa kelas 4

        $materials = Material::where('class_grade', $studentClass)
                             ->orderBy('created_at', 'desc')
                             ->get();

        return view('tugas', compact('materials', 'studentClass'));
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
}
