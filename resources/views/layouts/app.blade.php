<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Website Pembelajaran Siswa</title>
    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <!-- Tailwind CSS CDN untuk styling yang responsif -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            overflow-x: hidden; /* Mencegah scroll horizontal saat sidebar terbuka */
            min-height: 100vh; /* Memastikan body setidaknya setinggi viewport */
            display: flex;
            flex-direction: column;
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
            background-color: rgba(0, 0, 0, 0.5); /* Hitam transparan */
            z-index: 9998; /* Di bawah loading screen, di atas konten */
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0s linear 0.3s;
        }
        .black-overlay.show {
            opacity: 1;
            visibility: visible;
            transition: opacity 0.3s ease-in-out, visibility 0s linear 0s;
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

        /* Sidebar Menu Styling (untuk desktop/tablet) */
        #sidebarMenu {
            position: fixed;
            top: 0;
            right: -300px; /* Sembunyikan di luar layar */
            width: 300px; /* Lebar sidebar */
            height: 100%;
            background-color: #f7f7f7; /* Warna latar belakang menu */
            box-shadow: -4px 0 12px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            transition: right 0.3s ease-in-out; /* Animasi slide */
            padding-top: 60px; /* Ruang untuk tombol close */
            display: flex; /* Menggunakan flexbox untuk tata letak konten */
            flex-direction: column;
            align-items: flex-start; /* Rata kiri */
        }
        #sidebarMenu.open {
            right: 0; /* Tampilkan di layar */
        }
        .sidebar-menu-link {
            width: 100%; /* Agar link memenuhi lebar sidebar */
            padding: 15px 20px;
            color: #333;
            font-size: 1.1rem;
            text-decoration: none;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s ease-in-out;
        }
        .sidebar-menu-link:hover {
            background-color: #e0e0e0;
        }
        .sidebar-menu-link:last-child {
            border-bottom: none;
        }

        /* Bottom Menu Styling (untuk mobile) */
        #bottomMenu {
            position: fixed;
            bottom: 0; /* Selalu di bagian bawah di mobile */
            left: 0;
            width: 100%;
            background-color: #f7f7f7; /* Warna latar belakang menu */
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.2);
            z-index: 999; /* Di atas konten, di bawah overlay */
            padding: 10px 0;
            display: flex;
            justify-content: space-around; /* Rata tengah ikon */
            align-items: center;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            flex-wrap: wrap; /* Izinkan wrap untuk copyright */
        }
        .bottom-menu-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #333;
            font-size: 0.8rem;
            padding: 5px;
            border-radius: 8px;
            transition: background-color 0.2s ease-in-out;
            flex: 1; /* Agar item membagi ruang secara merata */
            min-width: 60px; /* Lebar minimum agar tidak terlalu sempit */
        }
        .bottom-menu-item:hover {
            background-color: #e0e0e0;
        }
        .bottom-menu-item img {
            width: 30px; /* Ukuran ikon di menu bawah */
            height: 30px;
            margin-bottom: 5px;
        }
        .bottom-menu-item .active-dot {
            width: 6px;
            height: 6px;
            background-color: #2563eb; /* Warna dot aktif */
            border-radius: 50%;
            margin-top: 3px;
            display: none; /* Sembunyikan secara default */
        }
        .bottom-menu-item.active .active-dot {
            display: block; /* Tampilkan jika aktif */
        }
        .bottom-menu-copyright {
            width: 100%;
            text-align: center;
            margin-top: 10px;
            color: #666; /* Warna teks copyright di mobile */
        }

        /* Tombol Close Menu (untuk sidebar) */
        #closeSidebarButton {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 2rem;
            color: #333;
            cursor: pointer;
            z-index: 10000;
        }

        /* Styling untuk "TEKS LOKASI PAGE" */
        .location-badge {
            position: absolute;
            top: 50px; /* Sesuaikan posisi vertikal */
            left: 50%;
            transform: translateX(-50%);
            background-color: #fbbf24; /* Kuning Tailwind 400 */
            color: #2c3e50; /* Warna teks */
            padding: 8px 25px;
            border-radius: 0 0 15px 15px; /* Melengkung di bawah */
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1010; /* Di atas header, di bawah menu */
            white-space: nowrap; /* Mencegah teks pecah baris */
        }

        /* Footer Styling (hanya untuk desktop) */
        .main-footer {
            background-color: #60a5fa; /* Biru muda */
            color: white;
            padding: 20px;
            text-align: center;
            /* Menggunakan flexbox untuk mendorong footer ke bawah */
            margin-top: auto; /* Mendorong ke bawah */
            width: 100%;
        }

        /* Media Queries untuk Responsif */
        @media (min-width: 768px) { /* Untuk desktop/tablet */
            #hamburgerButtonDesktop { /* Menggunakan ID baru */
                display: block; /* Tampilkan hamburger di desktop */
                width: 45px; /* Perbesar ukuran hamburger */
                height: 45px;
            }
            #hamburgerIconDesktop { /* Pastikan gambar ikon hamburger mengikuti ukuran tombol */
                width: 100%;
                height: 100%;
            }
            #bottomMenu {
                display: none !important; /* Sembunyikan menu bawah di desktop */
            }
            #sidebarMenu {
                display: flex; /* Tampilkan sidebar di desktop */
            }
            .main-footer { /* Tampilkan footer copyright di desktop */
                display: block;
            }
            /* Menyesuaikan posisi logo dan hamburger di header untuk desktop */
            .header-absolute .container {
                justify-content: space-between; /* Logo di kiri, hamburger di kanan */
                align-items: center; /* Pusatkan vertikal */
            }
            /* Memastikan teks lokasi page tetap di tengah */
            #pageLocationText {
                left: 50%;
                transform: translateX(-50%);
            }
        }

        @media (max-width: 767px) { /* Untuk mobile */
            #hamburgerButtonDesktop { /* Sembunyikan hamburger desktop di mobile */
                display: none !important;
            }
            /* Menghilangkan hamburger mobile dari header */
            .header-absolute .md\:hidden { /* Target div yang berisi hamburgerButtonMobile */
                display: none !important;
            }
            #bottomMenu {
                display: flex !important; /* Tampilkan menu bawah di mobile */
            }
            #sidebarMenu {
                display: none !important; /* Sembunyikan sidebar di mobile */
            }
            .main-footer { /* Sembunyikan footer copyright di mobile */
                display: none !important;
            }
            /* Menyesuaikan padding-bottom main untuk mobile agar tidak tertutup bottomMenu */
            main {
                padding-bottom: 150px !important; /* Sesuaikan dengan tinggi bottomMenu + copyright */
            }
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
        <nav class="container mx-auto flex items-center relative">
            {{-- Logo di kiri atas --}}
            <div class="flex items-center">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-10 h-10 mr-2"> {{-- Contoh logo --}}
                <a href="/" class="text-white text-2xl font-bold">Website Belajar</a>
            </div>

            {{-- "TEKS LOKASI PAGE" --}}
            <div id="pageLocationText" class="location-badge">
                TEKS LOKASI PAGE
            </div>

            {{-- Hamburger Menu for Desktop/Tablet (di paling kanan) --}}
            <div class="hidden md:block ml-auto"> {{-- Menggunakan ml-auto untuk mendorong ke kanan --}}
                <button id="hamburgerButtonDesktop" class="text-white focus:outline-none p-2 rounded-md hover:bg-blue-500 transition duration-300 ease-in-out">
                    <img id="hamburgerIconDesktop" src="{{ asset('images/humburger-before.png') }}" alt="Menu">
                </button>
            </div>

            {{-- Placeholder untuk mobile hamburger (INI DIHAPUS DARI HEADER) --}}
            {{-- <div class="md:hidden ml-auto">
                <button id="hamburgerButtonMobile" class="text-white focus:outline-none p-2 rounded-md hover:bg-blue-500 transition duration-300 ease-in-out">
                    <img id="hamburgerIconMobile" src="{{ asset('images/humburger-before.png') }}" alt="Menu" class="w-8 h-8">
                </button>
            </div> --}}
        </nav>
        {{-- Garis bawah header --}}
        <div class="header-line"></div>
    </header>

    {{-- Mobile Menu (Sidebar) - Untuk Desktop/Tablet --}}
    <div id="sidebarMenu">
        <button id="closeSidebarButton">X</button>
        <a href="{{ route('public.dashboard') }}" class="sidebar-menu-link">Menu</a>
        <a href="{{ route('public.diskusi') }}" class="sidebar-menu-link">Diskusi</a>
        <a href="{{ route('public.capaian') }}" class="sidebar-menu-link">Capaian Siswa</a>
        <a href="{{ route('public.materi') }}" class="sidebar-menu-link">Materi</a>
        <a href="{{ route('public.tugas') }}" class="sidebar-menu-link">Tugas</a>
    </div>

    {{-- Content Section --}}
    {{-- Tambahkan padding-top agar konten tidak tertutup header absolute --}}
    <main class="container mx-auto p-4" style="padding-top: 120px;"> {{-- Sesuaikan padding-top --}}
        @yield('content')
    </main>

    {{-- Bottom Menu - Untuk Mobile --}}
    <div id="bottomMenu">
        <a href="{{ route('public.dashboard') }}" class="bottom-menu-item" data-page="dashboard">
            <img src="{{ asset('images/home-icon.png') }}" alt="Home">
            <span>Home</span>
            <span class="active-dot"></span>
        </a>
        <a href="{{ route('public.diskusi') }}" class="bottom-menu-item" data-page="diskusi">
            <img src="{{ asset('images/chat-icon.png') }}" alt="Diskusi">
            <span>Diskusi</span>
            <span class="active-dot"></span>
        </a>
        <a href="{{ route('public.materi') }}" class="bottom-menu-item" data-page="materi">
            <img src="{{ asset('images/book-icon.png') }}" alt="Materi">
            <span>Materi</span>
            <span class="active-dot"></span>
        </a>
        <a href="{{ route('public.tugas') }}" class="bottom-menu-item" data-page="tugas">
            <img src="{{ asset('images/task-icon.png') }}" alt="Tugas">
            <span>Tugas</span>
            <span class="active-dot"></span>
        </a>
        <a href="{{ route('public.capaian') }}" class="bottom-menu-item" data-page="capaian">
            <img src="{{ asset('images/trophy-icon.png') }}" alt="Capaian">
            <span>Capaian</span>
            <span class="active-dot"></span>
        </a>
         {{-- Footer copyright di dalam bottom menu (hanya untuk mobile) --}}
        <p class="bottom-menu-copyright">&copy; COPYRIGHT BY FAQIH-NEXTAI 2025</p>
    </div>

    {{-- Footer (hanya untuk desktop) --}}
    <footer class="main-footer">
        <p>&copy; COPYRIGHT BY FAQIH-NEXTAI 2025</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingScreen = document.getElementById('loadingScreen');
            const loadingGif = document.getElementById('loadingGif');
            const blackOverlay = document.getElementById('blackOverlay');

            // Desktop Hamburger Menu
            const hamburgerButtonDesktop = document.getElementById('hamburgerButtonDesktop');
            const hamburgerIconDesktop = document.getElementById('hamburgerIconDesktop');
            const sidebarMenu = document.getElementById('sidebarMenu');
            const closeSidebarButton = document.getElementById('closeSidebarButton');
            const sidebarMenuLinks = document.querySelectorAll('.sidebar-menu-link');

            // Bottom Mobile Menu
            const bottomMenu = document.getElementById('bottomMenu');
            const bottomMenuItems = document.querySelectorAll('.bottom-menu-item');

            const pageLocationText = document.getElementById('pageLocationText');

            // Initial loading screen logic
            const minDisplayTime = 500; // 3 detik
            let startTime = Date.now();

            // Fungsi untuk menampilkan loading screen
            function showLoadingScreen() {
                loadingScreen.classList.remove('fade-out');
                loadingScreen.classList.add('show');
            }

            // Fungsi untuk menyembunyikan loading screen
            function hideLoadingScreen() {
                loadingScreen.classList.remove('show');
                loadingScreen.classList.add('fade-out');
                setTimeout(() => {
                    loadingScreen.style.display = 'none';
                    showRoleAlert(); // Panggil alert setelah loading screen selesai
                }, 1000); // Durasi fade-out
            }

            // Tampilkan loading screen saat halaman pertama kali dimuat
            showLoadingScreen();

            // Paksakan loading screen untuk hilang setelah minDisplayTime
            setTimeout(() => {
                hideLoadingScreen();
            }, minDisplayTime);

            // Menghapus loadingGif.onload dan loadingGif.onerror karena tidak lagi menjadi pemicu utama
            // loadingGif.onload = hideLoadingScreen;
            // loadingGif.onerror = function() {
            //     console.error("Gagal memuat GIF loading screen.");
            //     hideLoadingScreen(); // Tetap sembunyikan jika gagal
            // };


            // Hamburger Menu Toggle (untuk membuka sidebar di desktop/tablet)
            if (hamburgerButtonDesktop) {
                hamburgerButtonDesktop.addEventListener('click', function() {
                    sidebarMenu.classList.add('open'); // Buka sidebar
                    blackOverlay.classList.add('show'); // Tampilkan overlay
                    hamburgerIconDesktop.src = "{{ asset('images/humburger-after.png') }}"; // Ubah ikon ke 'X'
                });
            }

            // Tombol Close Menu (di dalam sidebar)
            if (closeSidebarButton) {
                closeSidebarButton.addEventListener('click', function() {
                    sidebarMenu.classList.remove('open'); // Tutup sidebar
                    blackOverlay.classList.remove('show'); // Sembunyikan overlay
                    hamburgerIconDesktop.src = "{{ asset('images/humburger-before.png') }}"; // Ubah ikon kembali ke hamburger
                });
            }

            // Logika untuk hamburgerButtonMobile dihapus karena tidak lagi di header
            // if (hamburgerButtonMobile) {
            //     hamburgerButtonMobile.addEventListener('click', function() {
            //         bottomMenu.classList.toggle('open'); // Toggle bottom menu
            //         blackOverlay.classList.toggle('show'); // Toggle overlay
            //     });
            // }


            // Klik overlay untuk menutup sidebar (jika terbuka)
            blackOverlay.addEventListener('click', function() {
                if (sidebarMenu.classList.contains('open')) {
                    sidebarMenu.classList.remove('open');
                    hamburgerIconDesktop.src = "{{ asset('images/humburger-before.png') }}";
                }
                // BottomMenu tidak perlu ditutup oleh overlay karena selalu terlihat di mobile
                blackOverlay.classList.remove('show');
            });


            // Sidebar Menu Link Click Handler
            sidebarMenuLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault(); // Mencegah navigasi langsung
                    const targetUrl = this.href;

                    // Tutup menu mobile dan overlay terlebih dahulu
                    sidebarMenu.classList.remove('open');
                    blackOverlay.classList.remove('show');
                    hamburgerIconDesktop.src = "{{ asset('images/humburger-before.png') }}";

                    // Tampilkan loading screen, lalu navigasi
                    startTime = Date.now(); // Reset start time untuk loading screen
                    showLoadingScreen();

                    // Setelah loading screen selesai, baru navigasi
                   // Navigasi segera setelah menampilkan loading screen
                    window.location.href = targetUrl;
                    // setTimeout tidak diperlukan lagi di sini jika ingin navigasi instan
                });
            });

            // Bottom Menu Item Click Handler (untuk mobile)
            bottomMenuItems.forEach(item => {
                item.addEventListener('click', function(event) {
                    event.preventDefault(); // Mencegah navigasi langsung
                    const targetUrl = this.href;

                    // Hapus kelas 'active' dari semua item
                    bottomMenuItems.forEach(el => el.classList.remove('active'));
                    // Tambahkan kelas 'active' ke item yang diklik
                    this.classList.add('active');

                    // Tampilkan loading screen, lalu navigasi
                    startTime = Date.now(); // Reset start time untuk loading screen
                    showLoadingScreen();

                    // Setelah loading screen selesai, baru navigasi
                    setTimeout(() => {
                        window.location.href = targetUrl; // Navigasi ke URL tujuan
                    }, minDisplayTime); // Durasi loading screen
                });
            });


            // Fungsi untuk menampilkan alert role
            function showRoleAlert() {
                let role = sessionStorage.getItem('siswa_role');
                if (!role) {
                    // Mengganti alert dengan modal kustom jika diperlukan di masa depan
                    role = prompt("Kamu kelas berapa? (contoh: 4, 5, atau 6)");
                    if (role) {
                        sessionStorage.setItem('siswa_role', role);
                        // Mengganti alert dengan modal kustom jika diperlukan di masa depan
                        alert(`Selamat datang siswa kelas ${role}!`);
                    } else {
                        // Mengganti alert dengan modal kustom jika diperlukan di masa depan
                        alert("Kamu belum memasukkan kelas. Beberapa konten mungkin tidak ditampilkan.");
                    }
                }
                // Panggil fungsi updateContentBasedOnRole jika ada
                if (typeof window.updateContentBasedOnRole === 'function') {
                    window.updateContentBasedOnRole(role);
                }
            }

            // Fungsi untuk mendapatkan role siswa yang sudah disimpan
            window.getStudentRole = function() {
                return sessionStorage.getItem('siswa_role');
            }

            // Fungsi untuk memperbarui teks lokasi halaman
            function updatePageLocationText() {
                const path = window.location.pathname;
                let pageName = "Halaman Utama"; // Default

                if (path.includes('diskusi')) {
                    pageName = "Diskusi Siswa";
                } else if (path.includes('capaian')) {
                    pageName = "Capaian Siswa";
                } else if (path.includes('materi')) {
                    pageName = "Materi Pembelajaran";
                } else if (path.includes('tugas')) {
                    pageName = "Tugas";
                } else if (path.includes('dashboard')) {
                    pageName = "Dashboard Utama";
                }
                pageLocationText.textContent = pageName;
            }

            // Fungsi untuk menandai item menu bawah yang aktif
            function setActiveBottomMenuItem() {
                const currentPage = window.location.pathname.split('/').pop(); // Ambil nama halaman dari URL
                bottomMenuItems.forEach(item => {
                    if (item.getAttribute('data-page') === currentPage) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });
            }


            // Panggil updatePageLocationText dan setActiveBottomMenuItem saat DOMContentLoaded
            updatePageLocationText();
            setActiveBottomMenuItem();
        });
    </script>
     @yield('scripts')
</body>
</html>
