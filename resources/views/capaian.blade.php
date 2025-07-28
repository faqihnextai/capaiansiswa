@extends('layouts.app')

@section('content')
    <h1 class="text-4xl font-extrabold text-center text-gray-800 mb-8"></h1>

    <div class="bg-white p-8 rounded-xl shadow-lg max-w-3xl mx-auto mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Filter Siswa</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <label for="filter_class_grade" class="block text-gray-700 text-sm font-semibold mb-2">Kelas:</label>
                <select id="filter_class_grade" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out">
                    <option value="">Pilih Kelas...</option>
                    @foreach($classes as $class_grade)
                        <option value="{{ $class_grade }}">Kelas {{ $class_grade }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter_group_id" class="block text-gray-700 text-sm font-semibold mb-2">Kelompok:</label>
                <select id="filter_group_id" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out" disabled>
                    <option value="">Pilih Kelompok...</option>
                    {{-- Options will be populated by JavaScript --}}
                </select>
            </div>

            <div>
                <label for="student_id_capaian" class="block text-gray-700 text-sm font-semibold mb-2">Nama Siswa:</label>
                <select id="student_id_capaian" class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out" required>
                    <option value="">Pilih Siswa...</option>
                    {{-- Options will be populated by JavaScript --}}
                </select>
            </div>
        </div>
    </div>

    <div id="student_achievements_display" class="bg-white p-8 rounded-xl shadow-lg max-w-3xl mx-auto hidden">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Capaian untuk <span id="selected_student_name" class="text-blue-600"></span></h2>
        <div id="achievements_list" class="space-y-4">
            {{-- Capaian siswa akan ditampilkan di sini oleh JavaScript --}}
        </div>
        <p id="no_achievements_message" class="text-center text-gray-500 mt-4 hidden">Belum ada capaian yang tercatat untuk siswa ini.</p>
    </div>

    <script>
        // Data dari Laravel
        const allStudents = @json($students);
        const allGroups = @json($groups);
        const dummyAchievements = @json($dummyAchievements); // Data dummy capaian

        const filterClassSelect = document.getElementById('filter_class_grade');
        const filterGroupSelect = document.getElementById('filter_group_id');
        const studentSelectCapaian = document.getElementById('student_id_capaian');
        const achievementsDisplay = document.getElementById('student_achievements_display');
        const selectedStudentNameSpan = document.getElementById('selected_student_name');
        const achievementsList = document.getElementById('achievements_list');
        const noAchievementsMessage = document.getElementById('no_achievements_message');

        // Fungsi untuk mengisi dropdown kelompok berdasarkan kelas yang dipilih
        function populateGroupFilter() {
            const selectedClass = filterClassSelect.value;
            filterGroupSelect.innerHTML = '<option value="">Pilih Kelompok...</option>';
            filterGroupSelect.disabled = true; // Nonaktifkan sampai kelas dipilih

            if (selectedClass) {
                const filteredGroups = allGroups.filter(group => group.class_grade == selectedClass);
                filteredGroups.forEach(group => {
                    const option = document.createElement('option');
                    option.value = group.id;
                    option.textContent = group.name;
                    filterGroupSelect.appendChild(option);
                });
                filterGroupSelect.disabled = false;
            }
            populateStudentSelect(); // Panggil juga untuk update siswa
        }

        // Fungsi untuk mengisi dropdown siswa berdasarkan filter kelas dan kelompok
        function populateStudentSelect() {
            const selectedClass = filterClassSelect.value;
            const selectedGroup = filterGroupSelect.value;

            studentSelectCapaian.innerHTML = '<option value="">Pilih Siswa...</option>'; // Bersihkan opsi sebelumnya

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
                studentSelectCapaian.appendChild(option);
            });

            // Sembunyikan tampilan capaian saat filter berubah
            achievementsDisplay.classList.add('hidden');
        }

        // Fungsi untuk menampilkan capaian siswa yang dipilih
        function displayStudentAchievements() {
            const selectedStudentId = studentSelectCapaian.value;
            achievementsList.innerHTML = ''; // Bersihkan daftar capaian sebelumnya
            noAchievementsMessage.classList.add('hidden'); // Sembunyikan pesan "belum ada capaian"

            if (selectedStudentId) {
                const selectedStudent = allStudents.find(student => student.id == selectedStudentId);
                if (selectedStudent) {
                    selectedStudentNameSpan.textContent = selectedStudent.name;
                    achievementsDisplay.classList.remove('hidden');

                    const studentAchievements = dummyAchievements[selectedStudentId]; // Ambil capaian dari data dummy

                    if (studentAchievements && studentAchievements.length > 0) {
                        studentAchievements.forEach(achievement => {
                            const achievementItem = document.createElement('div');
                            achievementItem.className = 'flex items-center space-x-3 p-3 bg-gray-50 rounded-lg shadow-sm';
                            achievementItem.innerHTML = `
                                <span class="text-2xl">${achievement.status ? '✅' : '❌'}</span>
                                <span class="text-gray-800 text-lg">${achievement.kriteria}</span>
                            `;
                            achievementsList.appendChild(achievementItem);
                        });
                    } else {
                        noAchievementsMessage.classList.remove('hidden');
                    }
                }
            } else {
                achievementsDisplay.classList.add('hidden'); // Sembunyikan jika tidak ada siswa yang dipilih
            }
        }

        // Event Listeners
        filterClassSelect.addEventListener('change', populateGroupFilter);
        filterGroupSelect.addEventListener('change', populateStudentSelect);
        studentSelectCapaian.addEventListener('change', displayStudentAchievements);

        // Panggil fungsi inisialisasi saat DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi filter kelas dan kelompok
            populateGroupFilter(); // Ini akan memicu populateStudentSelect juga

            // Jika ada role siswa dari sesi, coba terapkan filter awal
            const storedRole = window.getStudentRole();
            if (storedRole) {
                filterClassSelect.value = storedRole;
                populateGroupFilter(); // Panggil lagi untuk memicu filter kelompok dan siswa berdasarkan role awal
            }
        });
    </script>
@endsection
