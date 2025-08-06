<!-- resources/views/tugas.blade.php -->
@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-center text-gray-800">Daftar Tugas</h1>
    <p class="text-center mt-4 text-gray-600">Di sini akan ditampilkan daftar tugas dan materi pembelajaran yang relevan.
    </p>

    <div class="container mx-auto p-6 mt-8 bg-white rounded-xl shadow-lg">
        <!-- Filter Kelas, Kelompok, dan Siswa -->
        <div
            class="mb-6 p-4 bg-gray-50 rounded-lg shadow-inner flex flex-col md:flex-row items-center justify-center space-y-4 md:space-y-0 md:space-x-4">
            <div class="w-full md:w-1/3">
                <label for="filterClass" class="block text-gray-700 text-sm font-bold mb-2">Pilih Kelas:</label>
                <select id="filterClass"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class_grade)
                        <option value="{{ $class_grade }}" {{ (isset($selectedClass) && $selectedClass == $class_grade) ? 'selected' : '' }}>Kelas {{ $class_grade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-1/3">
                <label for="filterGroup" class="block text-gray-700 text-sm font-bold mb-2">Pilih Kelompok (sesuai
                    Kelas):</label>
                <select id="filterGroup"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Semua Kelompok</option>
                    {{-- Opsi kelompok akan dimuat via JavaScript --}}
                </select>
            </div>
            <div class="w-full md:w-1/3">
                <label for="filterStudent" class="block text-gray-700 text-sm font-bold mb-2">Pilih Siswa (sesuai
                    Kelompok):</label>
                <select id="filterStudent"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Semua Siswa</option>
                    {{-- Opsi siswa akan dimuat via JavaScript --}}
                </select>
            </div>
        </div>

        {{-- Bagian Materi Pembelajaran Dihilangkan --}}
        {{--
        <h3 class="text-2xl font-semibold text-gray-800 mb-4 mt-6">Materi Pembelajaran</h3>
        <div class="space-y-4" id="materials-list">
            -- Materi akan dirender oleh JavaScript --
        </div>
        <p class="text-gray-500 text-center" id="no-materials-message" style="display: none;">Belum ada materi yang tersedia
            untuk filter ini.</p>
        --}}


        <h3 class="text-2xl font-semibold text-gray-800 mb-4 mt-8">Daftar Tugas</h3>
        <div class="space-y-4" id="tasks-list">
            {{-- Tugas akan dirender oleh JavaScript --}}
        </div>
        {{-- Pesan ini selalu ada di DOM, visibilitasnya diatur oleh JS --}}
        <p class="text-gray-500 text-center" id="no-tasks-message" style="display: none;">Belum ada tugas yang tersedia
            untuk filter ini.</p>
    </div>

    <script>
        const allMaterials = @json($materials); // Tetap ada karena mungkin digunakan di tempat lain atau untuk referensi, tapi tidak ditampilkan.
        const allTasks = @json($tasks);
        const allGroups = @json($groups); // Semua data kelompok dari database
        const allStudents = @json($students); // Semua data siswa dari database

        const filterClassSelect = document.getElementById('filterClass');
        const filterGroupSelect = document.getElementById('filterGroup');
        const filterStudentSelect = document.getElementById('filterStudent'); // Elemen dropdown siswa
        // const materialsList = document.getElementById('materials-list'); // Dihapus
        // const noMaterialsMessage = document.getElementById('no-materials-message'); // Dihapus
        const tasksList = document.getElementById('tasks-list');
        const noTasksMessage = document.getElementById('no-tasks-message');

        let selectedClass = filterClassSelect.value;
        let selectedGroup = filterGroupSelect.value;
        let selectedStudent = filterStudentSelect.value; // Variabel untuk menyimpan siswa yang dipilih

        // Fungsi untuk memperbarui opsi kelompok berdasarkan kelas yang dipilih
        function updateGroupOptions(classGrade) {
            filterGroupSelect.innerHTML = '<option value="">Semua Kelompok</option>'; // Reset
            const groupsForClass = allGroups.filter(group => group.class_grade == classGrade);

            groupsForClass.forEach(group => {
                const option = document.createElement('option');
                option.value = group.id;
                option.textContent = group.name;
                filterGroupSelect.appendChild(option);
            });

            // Set kelompok yang dipilih sebelumnya jika ada dan valid
            if (sessionStorage.getItem('siswa_group') && groupsForClass.some(group => group.id == sessionStorage.getItem('siswa_group'))) {
                filterGroupSelect.value = sessionStorage.getItem('siswa_group');
            } else {
                filterGroupSelect.value = ""; // Reset jika tidak ada kelompok yang cocok
            }
            selectedGroup = filterGroupSelect.value; // Update selectedGroup setelah opsi dimuat
            updateStudentOptions(selectedGroup); // Perbarui opsi siswa saat kelompok berubah
        }

        // Fungsi untuk memperbarui opsi siswa berdasarkan kelompok yang dipilih
        function updateStudentOptions(groupId) {
            filterStudentSelect.innerHTML = '<option value="">Semua Siswa</option>'; // Reset
            let studentsForGroup = [];
            if (groupId) {
                // Cari kelompok yang cocok dan ambil siswanya
                const group = allGroups.find(g => g.id == groupId);
                if (group && group.students) {
                    studentsForGroup = group.students.sort((a, b) => a.name.localeCompare(b.name));
                }
            } else {
                // Jika "Semua Kelompok" dipilih, tampilkan semua siswa dari kelas yang dipilih
                if (selectedClass) {
                    studentsForGroup = allStudents.filter(student =>
                        allGroups.some(group => group.id === student.group_id && group.class_grade == selectedClass)
                    ).sort((a, b) => a.name.localeCompare(b.name));
                } else {
                    // Jika "Semua Kelas" dan "Semua Kelompok" dipilih, tampilkan semua siswa
                    studentsForGroup = allStudents.sort((a, b) => a.name.localeCompare(b.name));
                }
            }


            studentsForGroup.forEach(student => {
                const option = document.createElement('option');
                option.value = student.id;
                option.textContent = student.name;
                filterStudentSelect.appendChild(option);
            });

            // Set siswa yang dipilih sebelumnya jika ada dan valid
            if (sessionStorage.getItem('siswa_id') && studentsForGroup.some(student => student.id == sessionStorage.getItem('siswa_id'))) {
                filterStudentSelect.value = sessionStorage.getItem('siswa_id');
            } else {
                filterStudentSelect.value = ""; // Reset jika tidak ada siswa yang cocok
            }
            selectedStudent = filterStudentSelect.value; // Update selectedStudent setelah opsi dimuat
        }


        // Fungsi untuk memfilter dan menampilkan materi dan tugas
        function filterContent() {
            selectedClass = filterClassSelect.value;
            selectedGroup = filterGroupSelect.value;
            selectedStudent = filterStudentSelect.value;

            // Simpan pilihan di sessionStorage
            sessionStorage.setItem('siswa_role', selectedClass);
            sessionStorage.setItem('siswa_group', selectedGroup);
            sessionStorage.setItem('siswa_id', selectedStudent);


            // Filter Materi (Logika ini tetap ada, tapi tidak ada elemen DOM untuk menampilkannya)
            let filteredMaterials = allMaterials.filter(material => {
                const materialBelongsToSelectedClass = selectedClass === '' || material.class_grade == selectedClass;
                return materialBelongsToSelectedClass;
            });

            // materialsList.innerHTML = ''; // Dihapus
            // if (filteredMaterials.length > 0) {
            //     noMaterialsMessage.style.display = 'none'; // Dihapus
            //     filteredMaterials.forEach(material => {
            //         const materialDiv = document.createElement('div');
            //         materialDiv.classList.add('material-item', 'border', 'border-gray-200', 'p-4', 'rounded-md', 'shadow-sm', 'bg-gray-50');
            //         materialDiv.setAttribute('data-class', material.class_grade);
            //         let contentHtml = '';
            //         if (material.asset_type === 'link') {
            //             contentHtml = `<p class="text-sm text-gray-700 mt-1">Link: <a href="${material.content}" target="_blank" class="text-blue-500 hover:underline break-all">${material.content}</a></p>`;
            //         } else if (material.asset_type === 'file') {
            //             contentHtml = `<p class="text-sm text-gray-700 mt-1">File: <a href="{{ asset('storage/') }}/${material.content}" target="_blank" class="text-blue-500 hover:underline">${material.content.split('/').pop()}</a></p>`;
            //         } else {
            //             contentHtml = `<p class="text-sm text-gray-700 mt-1">Isi Teks: ${material.content}</p>`;
            //         }
            //         materialDiv.innerHTML = `
            //                 <p class="font-semibold text-gray-800 text-lg">${material.title}</p>
            //                 <p class="text-sm text-gray-600">Kelas: ${material.class_grade}</p>
            //                 ${contentHtml}
            //             `;
            //         materialsList.appendChild(materialDiv);
            //     });
            // } else {
            //     noMaterialsMessage.style.display = 'block'; // Dihapus
            // }

            // Filter Tugas
            let filteredTasks = allTasks.filter(task => {
                const taskBelongsToSelectedClass = selectedClass === '' || task.class_grade == selectedClass;

                // Logika baru untuk memfilter berdasarkan kelompok dan siswa
                let isTaskRelevantToSelection = false;

                if (selectedStudent !== '') {
                    // Jika siswa spesifik dipilih, cek apakah tugas ini ditujukan untuk siswa tersebut
                    isTaskRelevantToSelection = task.students.some(student => student.id == selectedStudent);
                } else if (selectedGroup !== '') {
                    // Jika kelompok spesifik dipilih (dan tidak ada siswa spesifik),
                    // cek apakah tugas ini ditujukan untuk kelompok tersebut
                    isTaskRelevantToSelection = task.groups.some(group => group.id == selectedGroup);
                } else {
                    // Jika tidak ada siswa atau kelompok spesifik yang dipilih (artinya "Semua Kelompok" atau "Semua Siswa"),
                    // tugas relevan jika tidak ada batasan kelompok/siswa atau jika tugas ditujukan untuk kelas yang dipilih.
                    // Ini mencakup tugas yang ditujukan ke semua siswa di kelas tersebut (jika tidak ada group/student relasi)
                    if (task.groups.length === 0 && task.students.length === 0) {
                        // Tugas adalah untuk semua siswa di kelasnya
                        isTaskRelevantToSelection = true;
                    } else if (task.groups.length > 0 && selectedGroup === '') {
                        // Jika tugas ditujukan ke beberapa kelompok, dan filter kelompok "Semua Kelompok",
                        // maka tugas ini relevan
                        isTaskRelevantToSelection = true;
                    } else if (task.students.length > 0 && selectedStudent === '') {
                        // Jika tugas ditujukan ke beberapa siswa, dan filter siswa "Semua Siswa",
                        // maka tugas ini relevan
                        isTaskRelevantToSelection = true;
                    }
                }

                return taskBelongsToSelectedClass && isTaskRelevantToSelection;
            });


            tasksList.innerHTML = ''; // Kosongkan daftar tugas
            if (filteredTasks.length > 0) {
                noTasksMessage.style.display = 'none'; // Sembunyikan pesan jika ada tugas
                filteredTasks.forEach(task => {
                    const taskDiv = document.createElement('div');
                    taskDiv.classList.add('task-item', 'border', 'border-gray-200', 'p-4', 'rounded-md', 'shadow-sm', 'bg-white');
                    taskDiv.setAttribute('data-class', task.class_grade);
                    // Tidak perlu lagi data-groups dan data-students di sini karena kita akan render teksnya langsung

                    const deadline = new Date(task.deadline).toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' });

                    // Logika untuk menampilkan kelompok dan siswa yang terkait
                    let groupsText = '';
                    if (task.groups && task.groups.length > 0) {
                        groupsText = task.groups.map(group => group.name).join(', ');
                    } else {
                        // Jika tidak ada kelompok spesifik yang terhubung, cek apakah ada siswa spesifik
                        // Jika tidak ada keduanya, berarti tugas ini untuk semua siswa di kelas tersebut
                        if (task.students && task.students.length === 0) {
                            groupsText = 'Semua Kelompok di Kelas Ini';
                        } else {
                            groupsText = 'Tidak Ditujukan ke Kelompok Spesifik';
                        }
                    }

                    let studentsText = '';
                    if (task.students && task.students.length > 0) {
                        studentsText = task.students.map(student => student.name).join(', ');
                    } else {
                        // Jika tidak ada siswa spesifik yang terhubung, dan juga tidak ada kelompok spesifik,
                        // maka tugas ini untuk semua siswa di kelas tersebut.
                        // Jika ada kelompok spesifik, tapi tidak ada siswa spesifik, berarti untuk semua siswa di kelompok itu.
                        if (task.groups && task.groups.length > 0) {
                            studentsText = 'Semua Siswa di Kelompok Terpilih';
                        } else {
                            studentsText = 'Semua Siswa di Kelas Ini';
                        }
                    }


                    taskDiv.innerHTML = `
                            <h4 class="font-semibold text-gray-800 text-lg">${task.title}</h4>
                            <p class="text-sm text-gray-600">Kelas: ${task.class_grade}</p>
                            <p class="text-sm text-gray-600">Untuk Kelompok: ${groupsText}</p>
                            <p class="text-sm text-gray-600">Untuk Siswa: ${studentsText}</p>
                            <p class="text-sm text-gray-600">Tenggat Waktu: ${deadline}</p>
                            <p id="countdown-${task.id}" class="text-sm font-bold text-red-500 mt-1"></p>
                         <a href="#" class="task-detail-link mt-3 inline-block bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                                Lihat Detail & Kerjakan
                            </a>
                        `;
                    // Langsung buat path URL menggunakan ID tugas
                    let detailUrl = `/tugas/${task.id}`;

                    // Tambahkan student_id sebagai query parameter jika ada siswa yang dipilih
                    if (selectedStudent && selectedStudent !== 'null' && selectedStudent !== 'undefined') {
                        detailUrl += `?student_id=${selectedStudent}`;
                        console.log('Debug: selectedStudent (saat membuat URL):', selectedStudent);
                    } else {
                        console.log('Debug: selectedStudent kosong atau tidak valid, tidak ditambahkan ke URL.');
                    }

                    // Tetapkan URL ke elemen <a>
                    taskDiv.querySelector('.task-detail-link').href = detailUrl;

                    // Debugging: Cek URL akhir yang ditetapkan ke link
                    console.log('Debug: Final URL untuk link tugas:', taskDiv.querySelector('.task-detail-link').href);
                    tasksList.appendChild(taskDiv);
                });
                // Setelah tugas dirender ulang, inisialisasi ulang countdown
                initCountdowns();
            } else {
                noTasksMessage.style.display = 'block'; // Tampilkan pesan jika tidak ada tugas
            }
        }

        // Fungsi inisialisasi countdown
        function initCountdowns() {
            document.querySelectorAll('[id^="countdown-"]').forEach(element => {
                const taskId = element.id.replace('countdown-', '');
                const task = allTasks.find(t => t.id == taskId); // Cari objek tugas yang sesuai

                if (task && element) {
                    const deadlineTime = new Date(task.deadline).getTime();

                    // Clear any existing interval for this element to prevent duplicates
                    if (element.countdownInterval) {
                        clearInterval(element.countdownInterval);
                    }

                    function updateSingleCountdown() {
                        const now = new Date().getTime();
                        const distance = deadlineTime - now;

                        if (distance < 0) {
                            element.innerHTML = "Waktu Habis!";
                            element.classList.remove('text-red-500');
                            element.classList.add('text-gray-500');
                            clearInterval(element.countdownInterval);
                            return;
                        }

                        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        element.innerHTML = `Sisa waktu: ${days}h ${hours}j ${minutes}m ${seconds}d`;

                        if (days < 1 && hours < 24) {
                            deadlineElement.classList.remove('text-blue-700');
                            deadlineElement.classList.add('text-yellow-700');
                        } else {
                            deadlineElement.classList.remove('text-yellow-700', 'text-red-700');
                            deadlineElement.classList.add('text-blue-700');
                        }
                    }

                    updateSingleCountdown(); // Panggil sekali saat inisialisasi
                    element.countdownInterval = setInterval(updateSingleCountdown, 1000); // Simpan interval ID
                }
            });
        }


        // Event Listeners untuk dropdown filter
        filterClassSelect.addEventListener('change', function () {
            updateGroupOptions(this.value); // Perbarui opsi kelompok saat kelas berubah
            filterContent();
        });

        filterGroupSelect.addEventListener('change', function () {
            updateStudentOptions(this.value); // Perbarui opsi siswa saat kelompok berubah
            filterContent();
        });

        filterStudentSelect.addEventListener('change', function () {
            filterContent();
        });

        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function () {
            // Set kelas dan kelompok awal dari sessionStorage atau parameter URL
            const urlParams = new URLSearchParams(window.location.search);
            const initialClass = urlParams.get('class') || sessionStorage.getItem('siswa_role') || '';
            const initialGroup = urlParams.get('group') || sessionStorage.getItem('siswa_group') || '';
            const initialStudent = urlParams.get('student') || sessionStorage.getItem('siswa_id') || '';

            filterClassSelect.value = initialClass;
            updateGroupOptions(initialClass); // Muat opsi kelompok berdasarkan kelas awal

            // Setelah updateGroupOptions selesai, barulah set initialGroup
            // Menggunakan setTimeout untuk memastikan DOM sudah diperbarui
            setTimeout(() => {
                filterGroupSelect.value = initialGroup; // Set kelompok awal
                updateStudentOptions(initialGroup); // Muat opsi siswa berdasarkan kelompok awal

                // Setelah updateStudentOptions selesai, barulah set initialStudent
                setTimeout(() => {
                    filterStudentSelect.value = initialStudent; // Set siswa awal
                    filterContent(); // Tampilkan konten yang difilter saat pertama kali dimuat
                }, 0);
            }, 0);
        });
    </script>
@endsection
