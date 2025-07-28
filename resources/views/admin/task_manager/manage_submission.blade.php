<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>Admin - Kelola & Cek Tugas Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
        .task-card {
            background-color: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .submission-row {
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 0;
        }
        .submission-row:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-white text-2xl font-bold">Dashboard Admin</h1>
            <nav>
                <a href="{{ route('admin.dashboard') }}" class="text-white hover:text-blue-200 mr-4">Dashboard</a>
                <a href="{{ route('admin.tasks.index') }}" class="text-white hover:text-blue-200 mr-4">Kembali ke Kelola Tugas</a>
                <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                        Logout
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="container mx-auto p-6 mt-8 bg-white rounded-xl shadow-lg">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Kelola & Cek Tugas Siswa</h2>
        <p class="text-gray-700 mb-4">Di sini Anda akan melihat daftar tugas yang sudah diberikan dan status pengerjaan oleh siswa.</p>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Sukses!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-gray-50 p-6 rounded-lg shadow-inner mb-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Daftar Tugas yang Diberikan</h3>

            @forelse($tasks as $task)
                <div class="task-card">
                    <h4 class="text-2xl font-bold text-blue-700 mb-2">{{ $task->title }}</h4>
                     <!-- Tombol Hapus Tugas -->
                        <form action="{{ route('admin.tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini dan semua pengumpulannya?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded text-sm transition duration-300">
                                Hapus Tugas
                            </button>
                        </form>
                    <p class="text-gray-600 mb-2">Kelas: {{ $task->class_grade }}</p>
                    {{-- Perubahan di sini untuk menampilkan kelompok --}}
                    <p class="text-gray-600 mb-2">
                        Kelompok:
                        @if($task->groups->isEmpty())
                            Semua Kelompok
                        @else
                            {{ $task->groups->pluck('name')->join(', ') }}
                        @endif
                    </p>
                    <p class="text-gray-600 mb-4">Tenggat Waktu: {{ \Carbon\Carbon::parse($task->deadline)->format('d M Y, H:i') }}</p>

                    <h5 class="text-lg font-semibold text-gray-700 mb-3">Status Pengumpulan:</h5>
                    @if($task->submissions->isEmpty())
                        <p class="text-gray-500">Belum ada siswa yang mengumpulkan tugas ini.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                <thead>
                                    <tr class="bg-gray-100 text-left text-gray-600 uppercase text-sm leading-normal">
                                        <th class="py-3 px-6 text-left">Siswa</th>
                                        <th class="py-3 px-6 text-left">Status</th>
                                        <th class="py-3 px-6 text-left">Waktu Kumpul</th>
                                        <th class="py-3 px-6 text-left">Nilai</th>
                                        <th class="py-3 px-6 text-left">Umpan Balik</th>
                                        <th class="py-3 px-6 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700 text-sm font-light">
                                    @foreach($task->submissions->sortBy('student.name') as $submission)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="py-3 px-6 text-left whitespace-nowrap">{{ $submission->student->name }}</td>
                                            <td class="py-3 px-6 text-left">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $submission->is_completed ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                                    {{ $submission->is_completed ? 'Sudah Kumpul' : 'Belum Kumpul' }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-6 text-left">{{ $submission->submitted_at ? \Carbon\Carbon::parse($submission->submitted_at)->format('d M Y, H:i') : '-' }}</td>
                                            <td class="py-3 px-6 text-left">
                                                <span id="score-{{ $submission->id }}">{{ $submission->score ?? 'N/A' }}</span>
                                            </td>
                                            <td class="py-3 px-6 text-left">
                                                <span id="feedback-{{ $submission->id }}">{{ $submission->feedback ?? '-' }}</span>
                                            </td>
                                            <td class="py-3 px-6 text-center">
                                                 <!-- Tombol Hapus Pengumpulan Tugas -->
                                                <form action="{{ route('admin.submissions.destroy', $submission->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumpulan tugas ini dari {{ $submission->student->name }}?');" class="inline-block ml-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="bg-red-400 hover:bg-red-500 text-white font-bold py-1 px-3 rounded text-xs transition duration-300">
                                                        Hapus
                                                    </button>
                                                </form>
                                                <button
                                                    class="open-score-modal bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded text-xs transition duration-300"
                                                    data-submission-id="{{ $submission->id }}"
                                                    data-current-score="{{ $submission->score }}"
                                                    data-current-feedback="{{ $submission->feedback }}"
                                                >
                                                    Nilai
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-gray-500">Belum ada tugas yang dibuat. Silakan buat tugas baru di halaman "Buat Soal & Input Tugas".</p>
            @endforelse
        </div>
    </main>

    <!-- Modal untuk Memberi Nilai -->
    <div id="scoreModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Beri Nilai Tugas</h3>
                <div class="mt-2 px-7 py-3">
                    <input type="hidden" id="modalSubmissionId">
                    <div class="mb-4">
                        <label for="modalScore" class="block text-gray-700 text-sm font-bold mb-2">Nilai (0-100):</label>
                        <input type="number" id="modalScore" min="0" max="100" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label for="modalFeedback" class="block text-gray-700 text-sm font-bold mb-2">Umpan Balik (Opsional):</label>
                        <textarea id="modalFeedback" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Berikan umpan balik untuk siswa..."></textarea>
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="saveScoreBtn" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Simpan
                    </button>
                    <button id="closeModalBtn" class="mt-3 px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scoreModal = document.getElementById('scoreModal');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const saveScoreBtn = document.getElementById('saveScoreBtn');
            const modalSubmissionId = document.getElementById('modalSubmissionId');
            const modalScore = document.getElementById('modalScore');
            const modalFeedback = document.getElementById('modalFeedback');

            document.querySelectorAll('.open-score-modal').forEach(button => {
                button.addEventListener('click', function() {
                    const submissionId = this.dataset.submissionId;
                    const currentScore = this.dataset.currentScore;
                    const currentFeedback = this.dataset.currentFeedback;

                    modalSubmissionId.value = submissionId;
                    modalScore.value = currentScore === 'N/A' ? '' : currentScore;
                    modalFeedback.value = currentFeedback === '-' ? '' : currentFeedback;
                    scoreModal.classList.remove('hidden');
                });
            });

            closeModalBtn.addEventListener('click', function() {
                scoreModal.classList.add('hidden');
            });

            saveScoreBtn.addEventListener('click', function() {
                const submissionId = modalSubmissionId.value;
                const score = modalScore.value;
                const feedback = modalFeedback.value;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/admin/tasks/submissions/${submissionId}/score`, { // Menggunakan route yang benar
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ score: score, feedback: feedback })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI dengan nilai dan feedback baru
                        document.getElementById(`score-${submissionId}`).innerText = data.score ?? 'N/A';
                        document.getElementById(`feedback-${submissionId}`).innerText = data.feedback ?? '-';
                        scoreModal.classList.add('hidden');
                        alert('Nilai berhasil disimpan!'); // Menggunakan alert sederhana untuk demo
                    } else {
                        alert('Gagal menyimpan nilai: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan nilai.');
                });
            });
        });
    </script>
</body>
</html>
