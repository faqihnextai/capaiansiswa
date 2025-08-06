@extends('layouts.app')
@section('content')
    <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Dashboard Utama Siswa</h1>

    {{-- Bagian 1: Gambar Slide Otomatis --}}
    <div class="relative w-full max-w-4xl mx-auto overflow-hidden rounded-xl shadow-lg mb-12">
        <div id="image-slider" class="flex transition-transform duration-500 ease-in-out">
            <div class="w-full flex-shrink-0">
                <img src="images/gambar1.jpg" alt="Slide 1" class="w-full h-auto object-cover rounded-xl border-4 border-yellow-400 shadow-gold-glow">
            </div>
            <div class="w-full flex-shrink-0">
                <img src="images/gambar2.jpg" alt="Slide 2" class="w-full h-auto object-cover rounded-xl border-4 border-yellow-400 shadow-gold-glow">
            </div>
            <div class="w-full flex-shrink-0">
                <img src="images/gambar3.jpg" alt="Slide 3" class="w-full h-auto object-cover rounded-xl border-4 border-yellow-400 shadow-gold-glow">
            </div>
        </div>
        <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
            <span class="dot w-3 h-3 bg-gray-300 rounded-full cursor-pointer" data-slide="0"></span>
            <span class="dot w-3 h-3 bg-gray-300 rounded-full cursor-pointer" data-slide="1"></span>
            <span class="dot w-3 h-3 bg-gray-300 rounded-full cursor-pointer" data-slide="2"></span>
        </div>
        <button id="prevSlide" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 p-2 rounded-full shadow-md text-gray-800 hover:bg-opacity-75 focus:outline-none">
            &#10094;
        </button>
        <button id="nextSlide" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 p-2 rounded-full shadow-md text-gray-800 hover:bg-opacity-75 focus:outline-none">
            &#10095;
        </button>
    </div>

    {{-- Bagian 2: Form Pencarian Capaian Siswa --}}
    <div class="bg-white p-8 rounded-xl shadow-lg max-w-3xl mx-auto mb-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Cari Capaian Siswa</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="filter_class_grade_dashboard" class="block text-gray-700 text-sm font-semibold mb-2">Kelas:</label>
                <select id="filter_class_grade_dashboard" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out">
                    <option value="">Pilih Kelas...</option>
                    @foreach($classes as $class_grade)
                        <option value="{{ $class_grade }}">Kelas {{ $class_grade }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter_group_id_dashboard" class="block text-gray-700 text-sm font-semibold mb-2">Kelompok:</label>
                <select id="filter_group_id_dashboard" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out" disabled>
                    <option value="">Pilih Kelompok...</option>
                    {{-- Options will be populated by JavaScript --}}
                </select>
            </div>
        </div>
        <div class="mb-6">
            <label for="student_id_dashboard" class="block text-gray-700 text-sm font-semibold mb-2">Nama Siswa:</label>
            <select id="student_id_dashboard" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out" required disabled>
                <option value="">Pilih Siswa...</option>
                {{-- Options will be populated by JavaScript --}}
            </select>
        </div>

        <div id="dashboard_student_achievements_display" class="bg-gray-50 p-6 rounded-xl shadow-inner hidden">
            <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">Capaian untuk <span id="dashboard_selected_student_name" class="text-blue-600"></span></h3>
            <div id="dashboard_achievements_list" class="space-y-3">
                {{-- Capaian siswa akan ditampilkan di sini oleh JavaScript --}}
            </div>
            <p id="dashboard_no_achievements_message" class="text-center text-gray-500 mt-4 hidden">Belum ada capaian yang tercatat untuk siswa ini.</p>
        </div>
    </div>

    {{-- Bagian 3: Biodata Mata Pelajaran Informatika --}}
    <div class="bg-white p-8 rounded-xl shadow-lg max-w-3xl mx-auto mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Tentang Mata Pelajaran Informatika</h2>
        <div class="text-gray-700 leading-relaxed">
            <p class="mb-4">Mata pelajaran Informatika diampu oleh **Faqih Baidowi**. Beliau adalah seorang pengajar yang berdedikasi untuk memperkenalkan dunia komputasi dan teknologi kepada siswa dengan cara yang interaktif dan menyenangkan.</p>
            <p class="mb-4">Dalam mata pelajaran ini, siswa akan belajar berbagai konsep dasar informatika, mulai dari pengenalan perangkat keras dan perangkat lunak, dasar-dasar pemrograman, keamanan digital, hingga etika berinternet.</p>
            <p class="mb-4">Tujuan utama mata pelajaran ini adalah membekali siswa dengan keterampilan berpikir komputasi, kreativitas dalam memecahkan masalah, serta kemampuan untuk beradaptasi dengan perkembangan teknologi yang pesat. Pembelajaran akan dilakukan melalui proyek-proyek praktis, diskusi kelompok, dan eksplorasi mandiri.</p>
            <p class="font-semibold">Mari bersama-sama menjelajahi potensi tak terbatas di dunia Informatika!</p>
        </div>
    </div>

    <style>
        /* Custom CSS for golden shadow effect */
        .shadow-gold-glow {
            box-shadow: 0 0 15px 5px rgba(255, 215, 0, 0.7), /* Emas */
                        0 0 30px 10px rgba(255, 215, 0, 0.5); /* Emas lebih luas */
        }
        /* Slider container to ensure images are side-by-side without wrapping */
        #image-slider {
            display: flex;
            width: 100%; /* Important for horizontal scrolling */
            transition: transform 0.5s ease-in-out;
        }
        #image-slider > div {
            flex: 0 0 100%; /* Each slide takes full width */
        }
        /* Story Notification Button Styling (Ini sudah dipindahkan ke app.blade.php) */
        /* .story-notification-button { ... } */
        /* .story-notification-button img { ... } */
        /* .story-notification-button .story-badge { ... } */

        /* Story Carousel Styling (Ini sudah diganti dengan halaman terpisah) */
        /* #storyCarousel img { ... } */
        /* #storyCarousel .caption { ... } */
        /* #storySlides > div { ... } */
        /* .progress-bar { ... } */
        /* .progress-bar-fill { ... } */
    </style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Bagian 1: Image Slider (sudah ada, tidak perlu diulang)
        const slider = document.getElementById('image-slider');
        const slides = slider.children;
        const totalSlides = slides.length;
        let currentIndex = 0;
        const slideIntervalTime = 2500; // 2.5 detik
        let slideInterval;

        const dotsContainer = document.querySelector('.absolute.bottom-4');
        const dots = dotsContainer.children;
        const prevButton = document.getElementById('prevSlide');
        const nextButton = document.getElementById('nextSlide');

        function updateSliderPosition() {
            slider.style.transform = `translateX(-${currentIndex * 100}%)`;
            updateDots();
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateSliderPosition();
        }

        function prevSlide() {
            currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
            updateSliderPosition();
        }

        function startSlider() {
            slideInterval = setInterval(nextSlide, slideIntervalTime);
        }

        function stopSlider() {
            clearInterval(slideInterval);
        }

        function updateDots() {
            for (let i = 0; i < dots.length; i++) {
                dots[i].classList.remove('bg-blue-500');
                dots[i].classList.add('bg-gray-300');
            }
            dots[currentIndex].classList.add('bg-blue-500');
            dots[currentIndex].classList.remove('bg-gray-300');
        }

        nextButton.addEventListener('click', () => {
            stopSlider();
            nextSlide();
            startSlider();
        });

        prevButton.addEventListener('click', () => {
            stopSlider();
            prevSlide();
            startSlider();
        });

        dotsContainer.addEventListener('click', (event) => {
            if (event.target.classList.contains('dot')) {
                stopSlider();
                currentIndex = parseInt(event.target.dataset.slide);
                updateSliderPosition();
                startSlider();
            }
        });

        startSlider();
        updateDots();


        // Bagian 2: Form Pencarian Capaian Siswa (sudah ada, tidak perlu diulang)
        const allStudents = @json($students);
        const allGroups = @json($groups);
        const dummyAchievements = @json($dummyAchievements);

        const filterClassSelectDashboard = document.getElementById('filter_class_grade_dashboard');
        const filterGroupSelectDashboard = document.getElementById('filter_group_id_dashboard');
        const studentSelectDashboard = document.getElementById('student_id_dashboard');
        const achievementsDisplayDashboard = document.getElementById('dashboard_student_achievements_display');
        const selectedStudentNameSpanDashboard = document.getElementById('dashboard_selected_student_name');
        const achievementsListDashboard = document.getElementById('dashboard_achievements_list');
        const noAchievementsMessageDashboard = document.getElementById('dashboard_no_achievements_message');

        function populateGroupFilterDashboard() {
            const selectedClass = filterClassSelectDashboard.value;
            filterGroupSelectDashboard.innerHTML = '<option value="">Pilih Kelompok...</option>';
            filterGroupSelectDashboard.disabled = true;
            studentSelectDashboard.innerHTML = '<option value="">Pilih Siswa...</option>';
            studentSelectDashboard.disabled = true;
            achievementsDisplayDashboard.classList.add('hidden');

            if (selectedClass) {
                const filteredGroups = allGroups.filter(group => group.class_grade == selectedClass);
                filteredGroups.forEach(group => {
                    const option = document.createElement('option');
                    option.value = group.id;
                    option.textContent = group.name;
                    filterGroupSelectDashboard.appendChild(option);
                });
                filterGroupSelectDashboard.disabled = false;
            }
            populateStudentSelectDashboard();
        }

        function populateStudentSelectDashboard() {
            const selectedClass = filterClassSelectDashboard.value;
            const selectedGroup = filterGroupSelectDashboard.value;

            studentSelectDashboard.innerHTML = '<option value="">Pilih Siswa...</option>';
            studentSelectDashboard.disabled = true;
            achievementsDisplayDashboard.classList.add('hidden');

            let filteredStudents = allStudents;

            if (selectedClass) {
                filteredStudents = filteredStudents.filter(student => student.group && student.group.class_grade == selectedClass);
            }

            if (selectedGroup) {
                filteredStudents = filteredStudents.filter(student => student.group_id == selectedGroup);
            }

            if (filteredStudents.length > 0) {
                filteredStudents.forEach(student => {
                    const option = document.createElement('option');
                    option.value = student.id;
                    option.textContent = student.name + (student.group ? ` (${student.group.name})` : '');
                    studentSelectDashboard.appendChild(option);
                });
                studentSelectDashboard.disabled = false;
            }
        }

        function displayStudentAchievementsDashboard() {
            const selectedStudentId = studentSelectDashboard.value;
            achievementsListDashboard.innerHTML = '';
            noAchievementsMessageDashboard.classList.add('hidden');

            if (selectedStudentId) {
                const selectedStudent = allStudents.find(student => student.id == selectedStudentId);
                if (selectedStudent) {
                    selectedStudentNameSpanDashboard.textContent = selectedStudent.name;
                    achievementsDisplayDashboard.classList.remove('hidden');

                    const studentAchievements = dummyAchievements[selectedStudentId];

                    if (studentAchievements && studentAchievements.length > 0) {
                        studentAchievements.forEach(achievement => {
                            const achievementItem = document.createElement('div');
                            achievementItem.className = 'flex items-center space-x-3 p-3 bg-white rounded-lg shadow-sm';
                            achievementItem.innerHTML = `
                                <span class="text-2xl">${achievement.status ? '✅' : '❌'}</span>
                                <span class="text-gray-800 text-lg">${achievement.kriteria}</span>
                            `;
                            achievementsListDashboard.appendChild(achievementItem);
                        });
                    } else {
                        noAchievementsMessageDashboard.classList.remove('hidden');
                    }
                }
            } else {
                achievementsDisplayDashboard.classList.add('hidden');
            }
        }

        filterClassSelectDashboard.addEventListener('change', populateGroupFilterDashboard);
        filterGroupSelectDashboard.addEventListener('change', populateStudentSelectDashboard);
        studentSelectDashboard.addEventListener('change', displayStudentAchievementsDashboard);

        const storedRole = window.getStudentRole();
        if (storedRole) {
            filterClassSelectDashboard.value = storedRole;
            populateGroupFilterDashboard();
        } else {
            populateGroupFilterDashboard();
        }

        });
</script>
@endsection
