<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Input Data Admin</title>
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
    </style>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-white text-2xl font-bold">Input Data Admin</h1>
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
            <button id="tabGroup" class="tab-button active" onclick="showSection('group')">Input Kelompok Baru</button>
            <button id="tabStars" class="tab-button ml-2" onclick="showSection('stars')">Input Nilai Bintang Siswa</button>
        </div>

        <!-- Section: Input Data Kelompok Siswa & Nilai Bintang Awal -->
        <div id="section_group" class="tab-content">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Input Data Kelompok Siswa</h2>

            {{-- Menampilkan pesan sukses dari session --}}
            @if(session('success_message_group'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Sukses!</strong>
                    <span class="block sm:inline">{{ session('success_message_group') }}</span>
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

            <form action="{{ route('admin.store.group') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="group_name" class="block text-gray-700 text-sm font-semibold mb-2">Nama Kelompok:</label>
                    <input type="text" id="group_name" name="group_name" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out" placeholder="Contoh: Kelompok A Kelas 4" required>
                </div>

                <div>
                    <label for="class_grade" class="block text-gray-700 text-sm font-semibold mb-2">Kelas:</label>
                    <select id="class_grade" name="class_grade" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out" required>
                        <option value="">Pilih Kelas</option>
                        <option value="4">Kelas 4</option>
                        <option value="5">Kelas 5</option>
                        <option value="6">Kelas 6</option>
                    </select>
                </div>

                <div id="student_list" class="space-y-4 border p-4 rounded-lg bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Anggota Kelompok</h3>
                    <div class="flex items-center space-x-4 mb-2">
                        <input type="text" name="students[0][name]" class="shadow-sm border rounded-lg flex-grow py-2 px-3 text-gray-700" placeholder="Nama Siswa" required>
                        <button type="button" onclick="removeStudentField(this)" class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-300 ease-in-out">Hapus</button>
                    </div>
                </div>
                <button type="button" onclick="addStudentField()" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition duration-300 ease-in-out">Tambah Siswa</button>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out transform hover:scale-105">
                    Simpan Kelompok
                </button>
            </form>
        </div>

        <!-- Section: Input Nilai Bintang Siswa (Perbarui/Individu) -->
        <div id="section_stars" class="tab-content hidden">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Input Nilai Bintang Siswa (Perbarui/Individu)</h2>

            {{-- Filter Kelas dan Kelompok --}}
            <div class="flex space-x-4 mb-6">
                <div>
                    <label for="filter_class_grade" class="block text-gray-700 text-sm font-semibold mb-2">Filter Kelas:</label>
                    <select id="filter_class_grade" class="shadow-sm border rounded-lg py-2 px-3 text-gray-700">
                        <option value="">Semua Kelas</option>
                        @php
                            if (is_string($classes)) {
                                $classes = explode(',', $classes);
                            }
                        @endphp

                        @foreach($classes as $class)
                            <option value="{{ $class }}">{{ $class }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter_group_id" class="block text-gray-700 text-sm font-semibold mb-2">Filter Kelompok:</label>
                    <select id="filter_group_id" class="shadow-sm border rounded-lg py-2 px-3 text-gray-700" disabled>
                        <option value="">Semua Kelompok</option>
                    </select>
                </div>
            </div>

            {{-- Menampilkan pesan sukses dari session --}}
            @if(session('success_message_stars'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Sukses!</strong>
                    <span class="block sm:inline">{{ session('success_message_stars') }}</span>
                </div>
            @endif
            @if ($errors->has('student_id') || $errors->has('stars'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->get('student_id') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        @foreach ($errors->get('stars') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.store.stars') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="student_id" class="block text-gray-700 text-sm font-semibold mb-2">Nama Siswa:</label>
                    <select id="student_id" name="student_id" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out" required>
                        <option value="">Pilih Siswa</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->name }} 
                                @if($student->group)
                                    ({{ $student->group->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pilih siswa yang ingin diperbarui nilai bintangnya.</p>
                </div>

                <div>
                    <label for="stars_rating" class="block text-gray-700 text-sm font-semibold mb-2">Nilai Bintang:</label>
                    <select id="stars_rating" name="stars" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out" required>
                        <option value="">Pilih Nilai Bintang</option>
                        <option value="1">⭐️</option>
                        <option value="2">⭐️⭐️</option>
                        <option value="3">⭐️⭐️⭐️</option>
                        <option value="4">⭐️⭐️⭐️⭐️</option>
                        <option value="5">⭐️⭐️⭐️⭐️⭐️</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out transform hover:scale-105">
                    Simpan Nilai Bintang
                </button>
            </form>
        </div>
    </main>

    <script>
        let studentCount = 1; // Untuk form input kelompok

        function addStudentField() {
            const studentList = document.getElementById('student_list');
            const newStudentField = document.createElement('div');
            newStudentField.className = 'flex items-center space-x-4 mb-2';
            // Perubahan di sini: Hanya input nama siswa, tanpa select bintang
            newStudentField.innerHTML = `
                <input type="text" name="students[${studentCount}][name]" class="shadow-sm border rounded-lg flex-grow py-2 px-3 text-gray-700" placeholder="Nama Siswa" required>
                <button type="button" onclick="removeStudentField(this)" class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-300 ease-in-out">Hapus</button>
            `;
            studentList.appendChild(newStudentField);
            studentCount++;
        }

        function removeStudentField(button) {
            button.parentNode.remove();
        }

        // --- Script untuk Tab Navigation ---
        function showSection(sectionId) {
            const sections = ['group', 'stars'];
            sections.forEach(id => {
                const sectionElement = document.getElementById(`section_${id}`);
                const tabButton = document.getElementById(`tab${id.charAt(0).toUpperCase() + id.slice(1)}`); // tabGroup, tabStars

                if (id === sectionId) {
                    sectionElement.classList.remove('hidden');
                    tabButton.classList.add('active');
                } else {
                    sectionElement.classList.add('hidden');
                    tabButton.classList.remove('active');
                }
            });

            // Update URL tanpa reload halaman
            const url = new URL(window.location);
            url.searchParams.set('section', sectionId);
            window.history.pushState({}, '', url);
        }

        // Panggil showSection saat halaman dimuat berdasarkan URL parameter
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const section = urlParams.get('section');
            if (section && (section === 'group' || section === 'stars')) {
                showSection(section);
            } else {
                showSection('group'); // Default ke tab 'group' jika tidak ada parameter atau salah
            }

            // Cek apakah ada pesan sukses dari session dan tampilkan tab yang sesuai
            @if(session('success_message_group'))
                showSection('group');
            @elseif(session('success_message_stars'))
                showSection('stars');
            @endif
        });

        // Data dari backend
        const allStudents = @json($students);
        const allGroups = @json($groups);

        // Populate group filter based on selected class
        function populateGroupFilter() {
            const classGrade = document.getElementById('filter_class_grade').value;
            const groupSelect = document.getElementById('filter_group_id');
            groupSelect.innerHTML = '<option value="">Semua Kelompok</option>';

            let filteredGroups = allGroups;
            if (classGrade) {
                filteredGroups = allGroups.filter(g => g.class_grade == classGrade);
            }

            if (filteredGroups.length > 0) {
                groupSelect.disabled = false;
                filteredGroups.forEach(group => {
                    groupSelect.innerHTML += `<option value="${group.id}">${group.name}</option>`;
                });
            } else {
                groupSelect.disabled = true;
            }
        }

        // Populate student select based on selected class and group
        function populateStudentSelect() {
            const classGrade = document.getElementById('filter_class_grade').value;
            const groupId = document.getElementById('filter_group_id').value;
            const studentSelect = document.getElementById('student_id');
            studentSelect.innerHTML = '<option value="">Pilih Siswa</option>';

            let filteredStudents = allStudents;
            if (classGrade) {
                filteredStudents = filteredStudents.filter(s => s.group && s.group.class_grade == classGrade);
            }
            if (groupId) {
                filteredStudents = filteredStudents.filter(s => s.group && s.group.id == groupId);
            }

            filteredStudents.forEach(student => {
                let groupName = student.group ? ` (${student.group.name})` : '';
                studentSelect.innerHTML += `<option value="${student.id}">${student.name}${groupName}</option>`;
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            populateGroupFilter();
            populateStudentSelect();

            document.getElementById('filter_class_grade').addEventListener('change', function() {
                populateGroupFilter();
                populateStudentSelect();
            });

            document.getElementById('filter_group_id').addEventListener('change', function() {
                populateStudentSelect();
            });
        });
    </script>
</body>
</html>
