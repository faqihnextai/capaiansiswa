<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Story;
use Carbon\Carbon; // Untuk manajemen waktu
use Illuminate\Support\Facades\Storage; // Untuk manajemen file

class StoryController extends Controller
{
    /**
     * Menampilkan form untuk membuat story baru (untuk admin).
     */
    public function create()
    {
        return view('admin.stories.create'); // Kita akan membuat view ini nanti
    }

    /**
     * Menyimpan story baru dari admin.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'story_images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Maks 2MB per gambar
                'captions.*' => 'nullable|string|max:255',
            ]);

            if ($request->hasFile('story_images')) {
                foreach ($request->file('story_images') as $key => $image) {
                    $imagePath = $image->store('stories', 'public'); // Simpan di storage/app/public/stories

                    Story::create([
                        'image_path' => $imagePath,
                        'caption' => $request->captions[$key] ?? null,
                        'expires_at' => Carbon::now()->addHours(16), // Story kadaluarsa dalam 16 jam
                    ]);
                }
            }

            // Mengembalikan respons JSON karena frontend menggunakan fetch API
            return response()->json([
                'success' => true,
                'message' => 'Story berhasil diunggah!',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $e->getMessage(),
                'errors' => $e->errors(),
            ], 422); // Unprocessable Entity
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengunggah story: ' . $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Mengambil story yang masih aktif untuk tampilan publik.
     */
    public function getActiveStories()
    {
        $activeStories = Story::where('expires_at', '>', Carbon::now())
                               ->orderBy('created_at', 'asc')
                               ->get();

        return response()->json($activeStories);
    }

    /**
     * Menghapus story (opsional, jika ingin ada fitur hapus story).
     */
    public function destroy(Story $story)
    {
        Storage::disk('public')->delete($story->image_path);
        $story->delete();

        return back()->with('success', 'Story berhasil dihapus.');
    }

        public function showPublicStoriesPage()
    {
        $activeStories = Story::where('expires_at', '>', Carbon::now())
                               ->orderBy('created_at', 'asc')
                               ->get();

        return view('public_stories', compact('activeStories'));
    }
    
}
