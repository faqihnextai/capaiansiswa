<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Publik</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
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
        <div class="container mx-auto">
            <h1 class="text-white text-3xl font-bold">Selamat Datang di Website Belajar!</h1>
        </div>
    </header>
    <main class="container mx-auto p-6 mt-8 bg-white rounded-xl shadow-lg">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Dashboard Utama Siswa</h2>
        <p class="text-gray-700 text-lg">Ini adalah halaman dashboard utama untuk siswa. Silakan gunakan menu navigasi di atas untuk menjelajahi fitur-fitur lainnya.</p>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-blue-100 p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-blue-800 mb-3">Diskusi Siswa</h3>
                <p class="text-blue-700">Lihat dan ikuti diskusi kelompok belajar.</p>
                <a href="{{ route('public.diskusi') }}" class="mt-4 inline-block text-blue-600 hover:underline">Lihat Diskusi &rarr;</a>
            </div>
            <div class="bg-green-100 p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-green-800 mb-3">Capaian Siswa</h3>
                <p class="text-green-700">Pantau progres dan capaian belajar Anda.</p>
                <a href="{{ route('public.capaian') }}" class="mt-4 inline-block text-green-600 hover:underline">Lihat Capaian &rarr;</a>
            </div>
            <div class="bg-yellow-100 p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-yellow-800 mb-3">Materi Pembelajaran</h3>
                <p class="text-yellow-700">Akses berbagai materi pembelajaran.</p>
                <a href="{{ route('public.materi') }}" class="mt-4 inline-block text-yellow-600 hover:underline">Lihat Materi &rarr;</a>
            </div>
            <div class="bg-purple-100 p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-purple-800 mb-3">Tugas</h3>
                <p class="text-purple-700">Lihat daftar tugas dan instruksi pengumpulan.</p>
                <a href="{{ route('public.tugas') }}" class="mt-4 inline-block text-purple-600 hover:underline">Lihat Tugas &rarr;</a>
            </div>
        </div>
    </main>
</body>
</html>
