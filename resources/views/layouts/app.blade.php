<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Pembelajaran Siswa</title>
    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <!-- Tailwind CSS CDN untuk styling yang responsif -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
        /* Header dengan posisi absolute dan warna biru */
        .header-absolute {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #2563eb; /* Biru Tailwind 600 */
            z-index: 1000; /* Pastikan di atas konten lain */
        }
        /* Garis bawah header dengan 3 warna */
        .header-line {
            height: 5px; /* Tinggi garis */
            background: linear-gradient(to right, #ffffff 33.33%, #10b981 33.33%, #10b981 66.66%, #fbbf24 66.66%, #fbbf24 100%);
        }
        /* Styling untuk loading screen agar menutupi seluruh layar */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9); /* Latar belakang gelap transparan */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999; /* Pastikan di atas semua elemen lain */
            color: white;
            flex-direction: column;
            opacity: 0; /* Mulai dengan transparan */
            visibility: hidden; /* Sembunyikan dari layout */
            transition: opacity 0.5s ease-out, visibility 0s linear 0.5s; /* Transisi opacity, lalu visibility */
        }
        .loading-screen.show {
            opacity: 1;
            visibility: visible;
            transition: opacity 0.5s ease-in, visibility 0s linear 0s;
        }
        /* Gambar GIF loading screen agar pas di layar */
        .loading-screen img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
        }
        /* Animasi untuk transisi fade-out loading screen */
        .loading-screen.fade-out {
            opacity: 0;
            transition: opacity 1s ease-out;
        }
        /* Styling untuk overlay hitam saat klik menu mobile */
        .black-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 1); /* Hitam penuh */
            z-index: 9998; /* Di bawah loading screen, di atas konten */
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s ease-in, visibility 0s linear 0.5s;
        }
        .black-overlay.show {
            opacity: 1;
            visibility: visible;
            transition: opacity 0.5s ease-in, visibility 0s linear 0s;
        }
        /* Styling untuk card di halaman menu */
        .student-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 24px;
            margin-bottom: 16px;
        }
        .student-card h3 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 16px;
        }
        .student-card ul {
            list-style: none;
            padding: 0;
        }
        .student-card ul li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        .student-card ul li:last-child {
            border-bottom: none;
        }
        .student-card .stars {
            color: #FFD700;
            font-size: 1.2rem;
        }
        /* Hamburger icon styling */
        #hamburgerIcon {
            width: 32px; /* Ukuran logo dada */
            height: 32px;
            transition: none; /* Nonaktifkan animasi rotasi */
        }
        #hamburgerButton.open #hamburgerIcon {
            transform: none; /* Nonaktifkan rotasi */
        }
    </style>
</head>
<body>
    {{-- Black Overlay --}}
    <div id="blackOverlay" class="black-overlay"></div>

    {{-- Loading Screen --}}
    <div id="loadingScreen" class="loading-screen">
        <img id="loadingGif" src="{{ asset('images/loading.gif') }}" alt="Loading..." style="width: 150px; height: 150px;">
        <p class="mt-4 text-lg">Memuat konten...</p>
    </div>

    {{-- Header --}}
    <header class="header-absolute p-4 shadow-md">
        <nav class="container mx-auto flex justify-between items-center">
            <div class="text-white text-2xl font-bold rounded-md px-3 py-1 bg-blue-500">
                <a href="/">Website Belajar</a>
            </div>
            {{-- Menu Desktop --}}
            <div class="hidden md:flex space-x-6">
                <a href="{{ route('public.dashboard') }}" class="text-white hover:text-blue-800 font-semibold transition duration-300 ease-in-out">Menu</a>
                <a href="{{ route('public.diskusi') }}" class="text-white hover:text-blue-800 font-semibold transition duration-300 ease-in-out">Diskusi</a>
                <a href="/capaian" class="text-white hover:text-blue-800 font-semibold transition duration-300 ease-in-out">Capaian</a>
                <a href="/materi" class="text-white hover:text-blue-800 font-semibold transition duration-300 ease-in-out">Materi Pembelajaran</a>
                <a href="/tugas" class="text-white hover:text-blue-800 font-semibold transition duration-300 ease-in-out">Tugas</a>
            </div>
            {{-- Hamburger Menu for Mobile --}}
            <div class="md:hidden">
                <button id="hamburgerButton" class="text-white focus:outline-none p-2 rounded-md hover:bg-blue-500 transition duration-300 ease-in-out">
                    <img id="hamburgerIcon" src="{{ asset('images/humburger-before.png') }}" alt="Menu" class="w-8 h-8">
                </button>
            </div>
        </nav>
        {{-- Garis bawah header --}}
        <div class="header-line"></div>
            {{-- Mobile Menu --}}
            <div id="mobileMenu" class="hidden md:hidden mt-2 bg-blue-400 rounded-b-lg shadow-lg">
                <a href="{{ route('public.dashboard') }}" class="mobile-menu-link block text-white px-4 py-3 hover:bg-blue-500 transition duration-300 ease-in-out rounded-t-lg">Menu</a>
                <a href="{{ route('public.diskusi') }}" class="mobile-menu-link block text-white px-4 py-3 hover:bg-blue-500 transition duration-300 ease-in-out">Diskusi</a>
                <a href="/capaian" class="mobile-menu-link block text-white px-4 py-3 hover:bg-blue-500 transition duration-300 ease-in-out">Capaian</a>
                <a href="/materi" class="mobile-menu-link block text-white px-4 py-3 hover:bg-blue-500 transition duration-300 ease-in-out">Materi Pembelajaran</a>
                <a href="/tugas" class="mobile-menu-link block text-white px-4 py-3 hover:bg-blue-500 transition duration-300 ease-in-out rounded-b-lg">Tugas</a>
            </div>
    </header>

    {{-- Content Section --}}
    {{-- Tambahkan padding-top agar konten tidak tertutup header absolute --}}
    <main class="container mx-auto p-4" style="padding-top: 80px;">
        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingScreen = document.getElementById('loadingScreen');
            const loadingGif = document.getElementById('loadingGif');
            const blackOverlay = document.getElementById('blackOverlay');
            const hamburgerButton = document.getElementById('hamburgerButton');
            const hamburgerIcon = document.getElementById('hamburgerIcon');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuLinks = document.querySelectorAll('.mobile-menu-link');

            // Initial loading screen logic
            const minDisplayTime = 3000;
            let startTime = Date.now();

            loadingGif.onload = function() {
                const elapsedTime = Date.now() - startTime;
                const remainingTime = minDisplayTime - elapsedTime;

                setTimeout(() => {
                    loadingScreen.classList.remove('show'); // Hide loading screen
                    loadingScreen.classList.add('fade-out');
                    setTimeout(() => {
                        loadingScreen.style.display = 'none';
                        showRoleAlert();
                    }, 1000);
                }, Math.max(0, remainingTime));
            };

            loadingGif.onerror = function() {
                console.error("Gagal memuat GIF loading screen.");
                loadingScreen.style.display = 'none';
                showRoleAlert();
            };

            // Show loading screen initially
            loadingScreen.classList.add('show');


            // Hamburger Menu Toggle
            hamburgerButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
                hamburgerButton.classList.toggle('open'); // Toggle class for icon animation

                if (hamburgerButton.classList.contains('open')) {
                    hamburgerIcon.src = "{{ asset('images/humburger-after.png') }}";
                } else {
                    hamburgerIcon.src = "{{ asset('images/humburger-before.png') }}";
                }
            });

            // Mobile Menu Link Click Handler
            mobileMenuLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault(); // Mencegah navigasi langsung
                    const targetUrl = this.href;

                    // 1. Tampilkan overlay hitam
                    blackOverlay.classList.add('show');

                    // 2. Setelah 0.5 detik, tampilkan loading screen, lalu navigasi
                    setTimeout(() => {
                        blackOverlay.classList.remove('show'); // Sembunyikan overlay hitam
                        blackOverlay.classList.add('fade-out'); // Tambahkan fade-out untuk overlay
                        loadingScreen.classList.remove('fade-out'); // Pastikan loading screen tidak fade-out
                        loadingScreen.classList.add('show'); // Tampilkan loading screen

                        // Setelah loading screen selesai, baru navigasi
                        // Menggunakan setTimeout lagi untuk simulasi durasi loading screen
                        setTimeout(() => {
                            window.location.href = targetUrl; // Navigasi ke URL tujuan
                        }, minDisplayTime); // Durasi loading screen
                    }, 500); // 0.5 detik untuk overlay hitam
                });
            });

            // Fungsi untuk menampilkan alert role
            function showRoleAlert() {
                let role = sessionStorage.getItem('siswa_role');
                if (!role) {
                    role = prompt("Kamu kelas berapa? (contoh: 4, 5, atau 6)");
                    if (role) {
                        sessionStorage.setItem('siswa_role', role);
                        alert(`Selamat datang siswa kelas ${role}!`);
                    } else {
                        alert("Kamu belum memasukkan kelas. Beberapa konten mungkin tidak ditampilkan.");
                    }
                }
                if (typeof window.updateContentBasedOnRole === 'function') {
                    window.updateContentBasedOnRole(role);
                }
            }

            // Fungsi untuk mendapatkan role siswa yang sudah disimpan
            window.getStudentRole = function() {
                return sessionStorage.getItem('siswa_role');
            }

            // Panggil fungsi updateContentBasedOnRole jika sudah ada role tersimpan
            // Ini akan dipanggil setelah loadingScreen selesai
            const storedRole = getStudentRole();
            if (storedRole) {
                 // updateContentBasedOnRole akan dipanggil setelah loading screen selesai
            }
        });
    </script>
</body>
</html>
