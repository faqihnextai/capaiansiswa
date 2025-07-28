<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>Kelola Tugas Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
    </style>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-white text-2xl font-bold">Dashboard Admin</h1>
            <nav>
                <a href="{{ route('admin.dashboard') }}" class="text-white hover:text-blue-200 mr-4">Dashboard</a>
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
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Kelola Tugas</h2>
        <p class="text-gray-700 mb-6">Pilih opsi di bawah untuk mengelola tugas:</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-blue-50 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <h3 class="text-xl font-semibold text-blue-800 mb-3">Membuat Soal & Input Tugas</h3>
                <p class="text-blue-700 mb-4">Buat soal-soal baru dan atur sebagai tugas yang akan dikerjakan siswa.</p>
                <a href="{{ route('admin.task_manager.create_question') }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                    Mulai Buat Soal & Tugas &rarr;
                </a>
            </div>

            <div class="bg-green-50 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <h3 class="text-xl font-semibold text-green-800 mb-3">Kelola & Cek Tugas Siswa</h3>
                <p class="text-green-700 mb-4">Lihat daftar siswa dan pantau status pengerjaan serta penilaian tugas mereka.</p>
                <a href="{{ route('admin.task_manager_submission') }}" class="inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                    Cek Status Tugas Siswa &rarr;
                </a>
            </div>
        </div>
    </main>
</body>
</html>