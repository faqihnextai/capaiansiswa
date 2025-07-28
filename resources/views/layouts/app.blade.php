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
            z-index: 1000; /* Di atas konten dan notifikasi story */
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

        .bottom-menu-item img {
            width: 24px; /* Ubah dari 30px menjadi 24px atau sesuai keinginan */
            height: 24px; /* Ubah dari 30px menjadi 24px atau sesuai keinginan */
            margin-bottom: 5px;
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
        @media (min-width: 768px) {
            /* Untuk desktop/tablet */
            #hamburgerButtonDesktop {
                /* Menggunakan ID baru */
                display: block; /* Tampilkan hamburger di desktop */
                width: 45px; /* Perbesar ukuran hamburger */
                height: 45px;
            }

            #hamburgerIconDesktop {
                /* Pastikan gambar ikon hamburger mengikuti ukuran tombol */
                width: 100%;
                height: 100%;
            }

            #bottomMenu {
                display: none !important; /* Sembunyikan menu bawah di desktop */
            }

            #sidebarMenu {
                display: flex; /* Tampilkan sidebar di desktop */
            }

            .main-footer {
                /* Tampilkan footer copyright di desktop */
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
            .story-notification-button {
                /* Sembunyikan di desktop, atau posisikan di tempat yang masuk akal */
                display: none !important;
            }
        }

        @media (max-width: 767px) {
            /* Untuk mobile */
            #hamburgerButtonDesktop {
                /* Sembunyikan hamburger desktop di mobile */
                display: none !important;
            }

            /* Menghilangkan hamburger mobile dari header */
            .header-absolute .md\:hidden {
                /* Target div yang berisi hamburgerButtonMobile */
                display: none !important;
            }

            #bottomMenu {
                display: flex !important; /* Tampilkan menu bawah di mobile */
            }

            #sidebarMenu {
                display: none !important; /* Sembunyikan sidebar di mobile */
            }

            .main-footer {
                /* Sembunyikan footer copyright di mobile */
                display: none !important;
            }

            /* Menyesuaikan padding-bottom main untuk mobile agar tidak tertutup bottomMenu */
            main {
                padding-bottom: 150px !important; /* Sesuaikan dengan tinggi bottomMenu + copyright */
            }

            /* Penyesuaian spesifik untuk tombol story di mobile,
           pastikan bottom-nya di atas footer */
            .story-notification-button {
                bottom: 90px; /* Disini disesuaikan agar di atas footer mobile (sekitar 10px padding + 24px ikon + 10px margin + 60px tinggi tombol = 94px) */
                right: 20px; /* Tetapkan posisi dari kanan */
                position: fixed; /* Penting agar posisinya tetap saat scroll */
                z-index: 990; /* Disesuaikan agar di atas konten tapi di bawah bottomMenu */
                display: flex; /* Pastikan selalu flex di mobile jika ada story */
            }
        }

        /* STORY NOTIFICATION BUTTON BASE STYLE */
        .story-notification-button {
            width: 60px; /* Ukuran tombol story */
            height: 60px;
            background-color: #fca5a5; /* Warna latar belakang menarik */
            border-radius: 50%; /* Bentuk lingkaran */
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            cursor: grab; /* Menunjukkan bisa digeser */
            transition: transform 0.2s ease-in-out; /* Untuk efek klik */
        }
        .story-notification-button img {
            width: 40px; /* Ukuran ikon di dalam tombol */
            height: 40px;
            border-radius: 50%;
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

    {{-- Tombol Notifikasi Story Mengambang --}}
    <div id="storyNotificationButton" class="story-notification-button hidden">
        <img src="{{ asset('images/story-icon.png') }}" alt="Story">
    </div>

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
            const blackOverlay = document.getElementById('blackOverlay');

            const hamburgerButtonDesktop = document.getElementById('hamburgerButtonDesktop');
            const hamburgerIconDesktop = document.getElementById('hamburgerIconDesktop');
            const sidebarMenu = document.getElementById('sidebarMenu');
            const closeSidebarButton = document.getElementById('closeSidebarButton');
            const sidebarMenuLinks = document.querySelectorAll('.sidebar-menu-link');

            const bottomMenu = document.getElementById('bottomMenu');
            const bottomMenuItems = document.querySelectorAll('.bottom-menu-item');

            const pageLocationText = document.getElementById('pageLocationText');

            const minDisplayTime = 500;
            let startTime = Date.now();

            function showLoadingScreen() {
                loadingScreen.classList.remove('fade-out');
                loadingScreen.classList.add('show');
            }

            function hideLoadingScreen() {
                loadingScreen.classList.remove('show');
                loadingScreen.classList.add('fade-out');
                setTimeout(() => {
                    loadingScreen.style.display = 'none';
                    showRoleAlert();
                }, 1000);
            }

            showLoadingScreen();

            setTimeout(() => {
                hideLoadingScreen();
            }, minDisplayTime);

            if (hamburgerButtonDesktop) {
                hamburgerButtonDesktop.addEventListener('click', function() {
                    sidebarMenu.classList.add('open');
                    blackOverlay.classList.add('show');
                    hamburgerIconDesktop.src = "{{ asset('images/humburger-after.png') }}";
                });
            }

            if (closeSidebarButton) {
                closeSidebarButton.addEventListener('click', function() {
                    sidebarMenu.classList.remove('open');
                    blackOverlay.classList.remove('show');
                    hamburgerIconDesktop.src = "{{ asset('images/humburger-before.png') }}";
                });
            }

            blackOverlay.addEventListener('click', function() {
                if (sidebarMenu.classList.contains('open')) {
                    sidebarMenu.classList.remove('open');
                    hamburgerIconDesktop.src = "{{ asset('images/humburger-before.png') }}";
                }
                blackOverlay.classList.remove('show');
            });

            sidebarMenuLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    const targetUrl = this.href;
                    sidebarMenu.classList.remove('open');
                    blackOverlay.classList.remove('show');
                    hamburgerIconDesktop.src = "{{ asset('images/humburger-before.png') }}";
                    startTime = Date.now();
                    showLoadingScreen();
                    window.location.href = targetUrl;
                });
            });

            bottomMenuItems.forEach(item => {
                item.addEventListener('click', function(event) {
                    event.preventDefault();
                    const targetUrl = this.href;
                    bottomMenuItems.forEach(el => el.classList.remove('active'));
                    this.classList.add('active');
                    startTime = Date.now();
                    showLoadingScreen();
                    setTimeout(() => {
                        window.location.href = targetUrl;
                    }, minDisplayTime);
                });
            });

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

            window.getStudentRole = function() {
                return sessionStorage.getItem('siswa_role');
            }

            function updatePageLocationText() {
                const path = window.location.pathname;
                let pageName = "Halaman Utama";

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

            function setActiveBottomMenuItem() {
                const currentPage = window.location.pathname.split('/').pop();
                bottomMenuItems.forEach(item => {
                    if (item.getAttribute('data-page') === currentPage) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });
            }

            updatePageLocationText();
            setActiveBottomMenuItem();

            // === Logic untuk Story Notification Button ===
            const storyNotificationButton = document.getElementById('storyNotificationButton');

            async function fetchActiveStoriesCount() {
                try {
                    const response = await fetch("{{ route('api.stories.active') }}");
                    const stories = await response.json();
                    return stories.length;
                } catch (error) {
                    console.error('Error fetching active stories:', error);
                    return 0; // Mengembalikan 0 jika ada error, agar ikon tidak tampil
                }
            }

            async function updateStoryNotification() {
                // Hanya tampilkan jika kita berada di public dashboard
                const currentPath = window.location.pathname;
                // Menggunakan startsWith karena public.dashboard bisa di '/' atau '/dashboard'
                const isDashboard = currentPath === '/' || currentPath.startsWith('{{ url()->route('public.dashboard', [], false) }}');

                if (!isDashboard) {
                    storyNotificationButton.classList.add('hidden');
                    return;
                }

                const hasSeenStory = sessionStorage.getItem('story_seen') === 'true';
                const activeStoryCount = await fetchActiveStoriesCount();

                if (activeStoryCount > 0 && !hasSeenStory) {
                    storyNotificationButton.classList.remove('hidden');
                } else {
                    storyNotificationButton.classList.add('hidden');
                }
            }

            // Memastikan storyNotificationButton ada sebelum menambahkan event listener
            if (storyNotificationButton) {
                storyNotificationButton.addEventListener('click', function(e) {
                    // Mencegah drag dari memicu klik navigasi
                    if (isDragging) {
                        e.stopPropagation(); // Mencegah event klik menyebar
                        return;
                    }
                    window.location.href = "{{ route('public.stories') }}";
                    sessionStorage.setItem('story_seen', 'true');
                });

                let isDragging = false;
                let currentX;
                let currentY;
                let initialX;
                let initialY;
                let xOffset = 0;
                let yOffset = 0;

                storyNotificationButton.addEventListener('touchstart', dragStart);
                storyNotificationButton.addEventListener('touchend', dragEnd);
                storyNotificationButton.addEventListener('touchmove', drag);

                storyNotificationButton.addEventListener('mousedown', dragStart);
                storyNotificationButton.addEventListener('mouseup', dragEnd);
                storyNotificationButton.addEventListener('mousemove', drag);

                function dragStart(e) {
                    if (e.type === "touchstart") {
                        initialX = e.touches[0].clientX - xOffset;
                        initialY = e.touches[0].clientY - yOffset;
                    } else {
                        initialX = e.clientX - xOffset;
                        initialY = e.clientY - yOffset;
                    }

                    // Hanya mulai drag jika target adalah tombol notifikasi itu sendiri atau anaknya
                    if (e.target === storyNotificationButton || storyNotificationButton.contains(e.target)) {
                        isDragging = true;
                    }
                }

                function dragEnd(e) {
                    initialX = currentX;
                    initialY = currentY;
                    isDragging = false;
                }

                function drag(e) {
                    if (isDragging) {
                        e.preventDefault(); // Mencegah scrolling saat drag

                        if (e.type === "touchmove") {
                            currentX = e.touches[0].clientX - initialX;
                            currentY = e.touches[0].clientY - initialY;
                        } else {
                            currentX = e.clientX - initialX;
                            currentY = e.clientY - initialY;
                        }

                        xOffset = currentX;
                        yOffset = currentY;

                        setTranslate(currentX, currentY, storyNotificationButton);
                    }
                }

                function setTranslate(xPos, yPos, el) {
                    el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
                }

                // Panggil fungsi ini saat halaman dimuat
                updateStoryNotification();

                // Pulihkan posisi tombol notifikasi dari sessionStorage saat halaman dimuat
                const savedX = sessionStorage.getItem('story_button_pos_x');
                const savedY = sessionStorage.getItem('story_button_pos_y');
                if (savedX && savedY) {
                    storyNotificationButton.style.left = savedX;
                    storyNotificationButton.style.top = savedY;
                }
                
                // Simpan posisi ke sessionStorage saat dihentikan
                storyNotificationButton.addEventListener('mouseup', () => {
                    sessionStorage.setItem('story_button_pos_x', storyNotificationButton.style.left);
                    sessionStorage.setItem('story_button_pos_y', storyNotificationButton.style.top);
                });
                storyNotificationButton.addEventListener('touchend', () => {
                    sessionStorage.setItem('story_button_pos_x', storyNotificationButton.style.left);
                    sessionStorage.setItem('story_button_pos_y', storyNotificationButton.style.top);
                });
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
