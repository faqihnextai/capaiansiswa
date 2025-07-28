<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>Input Capaian Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
        .tab-button {
            padding: 12px 24px;
            border-radius: 8px 8px 0 0;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #e2e8f0; /* bg-gray-200 */
            color: #4a5568; /* text-gray-700 */
        }
        .tab-button.active {
            background-color: #ffffff;
            color: #2b6cb0; /* text-blue-700 */
            box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
        }
        .tab-content {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 0 8px 8px 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .checkbox-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #f7fafc;
        }
        .checkbox-container:hover {
            background-color: #edf2f7;
        }
    </style>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-white text-2xl font-bold">Input Capaian Admin</h1>
            <nav>
                <a href="{{ route('admin.dashboard') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                    Kembali ke Dashboard
                </a>
                <form action="{{ route('admin.logout') }}" method="POST" class="inline-block ml-4">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                        Logout
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="container mx-auto p-6 mt-8">
        <!-- Tab Navigation -->
        <div class="flex mb-4">
            <button id="tabCriteria" class="tab-button active" onclick="showSection('criteria')">Input Kriteria Capaian</button>
            <button id="tabStatus" class="tab-button ml-2" onclick="showSection('status')">Update Status Capaian Siswa</button>
        </div>

        <!-- Section: Input Kriteria Capaian Per Kelas -->
        <div id="section_criteria" class="tab-content">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Input Kriteria Capaian Per Kelas</h2>

            {{-- Menampilkan pesan sukses dari session --}}
            @if(session('success_message_criteria'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Sukses!</strong>
                    <span class="block sm:inline">{{ session('success_message_criteria') }}</span>
                </div>
            @endif
            @if ($errors->any() && session()->has('section') && session('section') === 'criteria')
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.achievements.store.criteria') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="criteria_class_grade" class="block text-gray-700 text-sm font-semibold mb-2">Kelas:</label>
                    <select id="criteria_class_grade" name="class_grade" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out" required>
                        <option value="">Pilih Kelas</option>
                        @foreach($classes as $class_grade)
                            <option value="{{ $class_grade }}">Kelas {{ $class_grade }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="criteria_description" class="block text-gray-700 text-sm font-semibold mb-2">Deskripsi Capaian:</label>
                    <textarea id="criteria_description" name="description" rows="3" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out" placeholder="Contoh: Mengerjakan Tugas Matematika Bab 1" required></textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out transform hover:scale-105">
                    Simpan Kriteria Capaian
                </button>
            </form>

            <div class="mt-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Daftar Kriteria Capaian yang Sudah Ada:</h3>
                @if($achievements->isNotEmpty())
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        @foreach($achievements as $achievement)
                            <li>Kelas {{ $achievement->class_grade }}: {{ $achievement->description }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500 italic">Belum ada kriteria capaian yang diinput.</p>
                @endif
            </div>
        </div>

        <!-- Section: Update Status Capaian Siswa -->
        <div id="section_status" class="tab-content hidden">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Update Status Capaian Siswa</h2>

            {{-- Menampilkan pesan sukses/error dari AJAX --}}
            <div id="ajax_message" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold"></strong>
                <span class="block sm:inline"></span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="filter_class_grade_ach" class="block text-gray-700 text-sm font-semibold mb-2">Filter Kelas:</label>
                    <select id="filter_class_grade_ach" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class_grade)
                            <option value="{{ $class_grade }}">Kelas {{ $class_grade }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter_group_id_ach" class="block text-gray-700 text-sm font-semibold mb-2">Filter Kelompok:</label>
                    <select id="filter_group_id_ach" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out" disabled>
                        <option value="">Pilih Kelompok...</option>
                    </select>
                </div>

                <div>
                    <label for="student_id_achievements" class="block text-gray-700 text-sm font-semibold mb-2">Nama Siswa:</label>
                    <select id="student_id_achievements" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out" required>
                        <option value="">Pilih Siswa...</option>
                    </select>
                </div>
            </div>

            <div id="student_achievements_checklist" class="mt-8 space-y-4">
                <p class="text-center text-gray-500 italic" id="select_student_prompt">Pilih siswa untuk melihat dan memperbarui capaian.</p>
                {{-- Daftar capaian dengan checkbox akan dimuat di sini oleh JavaScript --}}
            </div>
            <p id="no_achievements_for_student" class="text-center text-gray-500 mt-4 hidden">Belum ada kriteria capaian yang ditetapkan untuk siswa ini.</p>
        </div>
    </main>

    <script>
        // Data dari Laravel
        const allStudents = @json($students);
        const allGroups = @json($groups);
        const allAchievementsCriteria = @json($achievements); // Semua kriteria capaian

        // --- Script untuk Tab Navigation (tetap sama) ---
        function showSection(sectionId) {
            const sections = ['criteria', 'status'];
            sections.forEach(id => {
                const sectionElement = document.getElementById(`section_${id}`);
                const tabButton = document.getElementById(`tab${id.charAt(0).toUpperCase() + id.slice(1)}`);

                if (id === sectionId) {
                    sectionElement.classList.remove('hidden');
                    tabButton.classList.add('active');
                } else {
                    sectionElement.classList.add('hidden');
                    tabButton.classList.remove('active');
                }
            });

            const url = new URL(window.location);
            url.searchParams.set('section', sectionId);
            window.history.pushState({}, '', url);
        }

        // --- Script untuk Filter Siswa di Tab "Update Status Capaian Siswa" ---
        const filterClassSelectAch = document.getElementById('filter_class_grade_ach');
        const filterGroupSelectAch = document.getElementById('filter_group_id_ach');
        const studentSelectAchievements = document.getElementById('student_id_achievements');
        const studentAchievementsChecklist = document.getElementById('student_achievements_checklist');
        const selectStudentPrompt = document.getElementById('select_student_prompt');
        const noAchievementsForStudent = document.getElementById('no_achievements_for_student');
        const ajaxMessage = document.getElementById('ajax_message');

        function populateGroupFilterAch() {
            const selectedClass = filterClassSelectAch.value;
            filterGroupSelectAch.innerHTML = '<option value="">Pilih Kelompok...</option>';
            filterGroupSelectAch.disabled = true;

            if (selectedClass) {
                const filteredGroups = allGroups.filter(group => group.class_grade == selectedClass);
                filteredGroups.forEach(group => {
                    const option = document.createElement('option');
                    option.value = group.id;
                    option.textContent = group.name;
                    filterGroupSelectAch.appendChild(option);
                });
                filterGroupSelectAch.disabled = false;
            }
            populateStudentSelectAch();
        }

        function populateStudentSelectAch() {
            const selectedClass = filterClassSelectAch.value;
            const selectedGroup = filterGroupSelectAch.value;

            studentSelectAchievements.innerHTML = '<option value="">Pilih Siswa...</option>';

            let filteredStudents = allStudents;

            if (selectedClass) {
                filteredStudents = filteredStudents.filter(student => student.group && student.group.class_grade == selectedClass);
            }

            if (selectedGroup) {
                filteredStudents = filteredStudents.filter(student => student.group_id == selectedGroup);
            }

            filteredStudents.forEach(student => {
                const option = document.createElement('option');
                option.value = student.id;
                option.textContent = student.name + (student.group ? ` (${student.group.name})` : '');
                studentSelectAchievements.appendChild(option);
            });

            // Sembunyikan checklist dan tampilkan prompt
            studentAchievementsChecklist.innerHTML = '';
            selectStudentPrompt.classList.remove('hidden');
            noAchievementsForStudent.classList.add('hidden');
        }

        async function loadStudentAchievementsChecklist() {
            const studentId = studentSelectAchievements.value;
            studentAchievementsChecklist.innerHTML = ''; // Bersihkan daftar sebelumnya
            selectStudentPrompt.classList.add('hidden');
            noAchievementsForStudent.classList.add('hidden');

            if (!studentId) {
                selectStudentPrompt.classList.remove('hidden');
                return;
            }

            // Dapatkan kriteria capaian untuk kelas siswa yang dipilih
            const selectedStudent = allStudents.find(s => s.id == studentId);
            if (!selectedStudent || !selectedStudent.group) {
                noAchievementsForStudent.classList.remove('hidden');
                return;
            }

            const classGrade = selectedStudent.group.class_grade;
            const achievementsForClass = allAchievementsCriteria.filter(ach => ach.class_grade == classGrade);

            if (achievementsForClass.length === 0) {
                noAchievementsForStudent.classList.remove('hidden');
                return;
            }

            // Ambil status capaian siswa dari server (atau bisa juga dari data yang sudah dimuat)
            // Untuk saat ini, kita akan simulasikan dengan data yang ada di `capaian()` method
            // Nanti, ini perlu di-fetch dari endpoint API jika data StudentAchievement sangat banyak
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content'); // Pastikan ada meta csrf-token di app.blade.php

            try {
                // Fetch data capaian siswa secara spesifik
                const response = await fetch(`/api/student-achievements/${studentId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken // Jika menggunakan CSRF token untuk API GET request
                    }
                });
                const data = await response.json();

                if (data.success && data.achievements.length > 0) {
                    data.achievements.forEach(ach => {
                        const checkboxContainer = document.createElement('div');
                        checkboxContainer.className = 'checkbox-container';
                        checkboxContainer.innerHTML = `
                            <label for="ach_${ach.id}" class="text-gray-800 text-lg cursor-pointer flex-grow">${ach.description}</label>
                            <input type="checkbox" id="ach_${ach.id}" data-student-id="${studentId}" data-achievement-id="${ach.id}" ${ach.is_completed ? 'checked' : ''} class="h-6 w-6 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                        `;
                        studentAchievementsChecklist.appendChild(checkboxContainer);
                    });

                    // Tambahkan event listener untuk setiap checkbox
                    studentAchievementsChecklist.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                        checkbox.addEventListener('change', updateAchievementStatus);
                    });
                } else {
                    noAchievementsForStudent.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error fetching student achievements:', error);
                noAchievementsForStudent.classList.remove('hidden');
                showMessage('Error', 'Gagal memuat capaian siswa.', 'bg-red-100 border-red-400 text-red-700');
            }
        }

        async function updateAchievementStatus(event) {
            const checkbox = event.target;
            const studentId = checkbox.dataset.studentId;
            const achievementId = checkbox.dataset.achievementId;
            const isCompleted = checkbox.checked;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const response = await fetch('{{ route('admin.achievements.update.status') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        student_id: studentId,
                        achievement_id: achievementId,
                        is_completed: isCompleted
                    })
                });
                const data = await response.json();

                if (data.success) {
                    showMessage('Sukses!', data.message, 'bg-green-100 border-green-400 text-green-700');
                } else {
                    showMessage('Error!', data.message, 'bg-red-100 border-red-400 text-red-700');
                }
            } catch (error) {
                console.error('Error updating achievement status:', error);
                showMessage('Error!', 'Terjadi kesalahan saat memperbarui status.', 'bg-red-100 border-red-400 text-red-700');
            }
        }

        function showMessage(title, message, classes) {
            ajaxMessage.className = classes + ' px-4 py-3 rounded relative mb-4';
            ajaxMessage.querySelector('strong').textContent = title;
            ajaxMessage.querySelector('span').textContent = message;
            ajaxMessage.classList.remove('hidden');
            setTimeout(() => {
                ajaxMessage.classList.add('hidden');
            }, 5000); // Sembunyikan setelah 5 detik
        }

        // Event Listeners untuk filter dan pemilihan siswa
        filterClassSelectAch.addEventListener('change', populateGroupFilterAch);
        filterGroupSelectAch.addEventListener('change', populateStudentSelectAch);
        studentSelectAchievements.addEventListener('change', loadStudentAchievementsChecklist);

        // Panggil fungsi inisialisasi saat DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi filter siswa di tab "Update Status Capaian Siswa"
            populateGroupFilterAch();

            // Cek apakah ada pesan sukses/error dari session dan tampilkan tab yang sesuai
            const urlParams = new URLSearchParams(window.location.search);
            const section = urlParams.get('section');
            const successCriteria = "{{ session('success_message_criteria') }}";

            if (successCriteria) {
                showSection('criteria');
            } else if (section && (section === 'criteria' || section === 'status')) {
                showSection(section);
            } else {
                showSection('criteria'); // Default ke tab 'criteria'
            }
        });
    </script>
</body>
</html>
