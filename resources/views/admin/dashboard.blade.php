<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>Dashboard Admin</title>
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
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                        Logout
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="container mx-auto p-6 mt-8 bg-white rounded-xl shadow-lg">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Selamat Datang, Admin!</h2>
        <p class="text-gray-700 mb-4">Ini adalah halaman dashboard admin. Di sini Anda bisa mengelola:</p>
        <ul class="list-disc list-inside text-gray-600 space-y-2">
            <li>Kelompok dan nilai bintang siswa</li>
            <li>Poin tugas siswa (ceklist)</li>
            <li>Aset pembelajaran</li>
            <li>Data kelas</li>
        </ul>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-blue-100 p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-blue-800 mb-3">Kelola Siswa & Kelompok</h3>
                <p class="text-blue-700">Tambahkan, edit, dan hapus data siswa serta kelompok belajar mereka.</p>
                <a href="{{ route('admin.input') }}" class="mt-4 inline-block text-blue-600 hover:underline">Input Data &rarr;</a>
            </div>
            <div class="bg-green-100 p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-green-800 mb-3">Kelola Tugas & Poin</h3>
                <p class="text-green-700">Berikan poin ceklist untuk tugas yang sudah disubmit siswa.</p>
                <a href="{{ route(name:'admin.tasks.index') }}" class="mt-4 inline-block text-green-600 hover:underline">Lihat Detail &rarr;</a>
            </div>
            <div class="bg-yellow-100 p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-yellow-800 mb-3">Kelola Materi & Aset</h3>
                <p class="text-yellow-700">Unggah dan atur aset pembelajaran seperti dokumen atau video.</p>
                <a href="{{ route('admin.materials.show') }}" class="mt-4 inline-block text-yellow-600 hover:underline">Kelola Aset &rarr;</a>
            </div>
            <div class="bg-purple-100 p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-purple-800 mb-3">Kelola Capaian Siswa</h3>
                <p class="text-purple-700">Input dan kelola capaian siswa secara detail.</p>
                <a href="{{ route('admin.achievements.input') }}" class="mt-4 inline-block text-purple-600 hover:underline">Input Capaian &rarr;</a>
            </div>
        </div>
    </main>
</body>
</html>
