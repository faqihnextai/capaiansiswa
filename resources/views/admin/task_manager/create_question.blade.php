<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>Buat Soal & Tugas Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }

        .question-block {
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            background-color: #fff;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body class="bg-gray-100">
    <header class="bg-blue-600 p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-white text-2xl font-bold">Dashboard Admin</h1>
            <nav>
                <a href="{{ route('admin.dashboard') }}" class="text-white hover:text-blue-200 mr-4">Dashboard</a>
                <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                        Logout
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="container mx-auto p-6 mt-8 bg-white rounded-xl shadow-lg">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Buat Soal & Input Tugas Baru</h2>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Sukses!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error Validasi!</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="createTaskForm" action="{{ route('admin.tasks.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-6 p-6 bg-blue-50 rounded-lg shadow-sm">
                <h3 class="text-xl font-semibold text-blue-800 mb-4">Detail Tugas</h3>
                <div class="mb-4">
                    <label for="task_title" class="block text-gray-700 text-sm font-bold mb-2">Judul Tugas:</label>
                    <input type="text" id="task_title" name="task_title"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                </div>

                <div class="mb-4">
                    <label for="class_grade" class="block text-gray-700 text-sm font-bold mb-2">Kelas:</label>
                    <select id="class_grade" name="class_grade"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                        <option value="">Pilih Kelas</option>
                        <option value="4">Kelas 4</option>
                        <option value="5">Kelas 5</option>
                        <option value="6">Kelas 6</option>
                    </select>
                </div>

                <!-- START: Perubahan untuk pemilihan kelompok dengan checkboxes -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Kelompok (Pilih beberapa,
                        Opsional):</label>
                    <div class="border rounded p-3 bg-white max-h-48 overflow-y-auto">
                        <!-- Checkbox untuk "Semua Kelompok" -->
                        <label class="inline-flex items-center mb-2 mr-4">
                            <input type="checkbox" id="select_all_groups" class="form-checkbox text-blue-600 rounded">
                            <span class="ml-2 text-gray-700 font-semibold">Semua Kelompok</span>
                        </label>
                        <div id="group_checkboxes_container"
                            class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                            <!-- Opsi kelompok akan diisi oleh JavaScript -->
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Pilih satu atau lebih kelompok.</p>
                </div>
                <!-- END: Perubahan untuk pemilihan kelompok dengan checkboxes -->

                <!-- START: Tambahan untuk pemilihan siswa dengan checkboxes -->
                <div class="mb-6 p-6 bg-yellow-50 rounded-lg shadow-sm mt-6">
                    <h3 class="text-xl font-semibold text-yellow-800 mb-4">Detail Siswa</h3>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Siswa (Pilih beberapa, Opsional):</label>
                    <div class="border rounded p-3 bg-white max-h-48 overflow-y-auto">
                        <label class="inline-flex items-center mb-2 mr-4">
                            <input type="checkbox" id="select_all_students" class="form-checkbox text-blue-600 rounded">
                            <span class="ml-2 text-gray-700 font-semibold">Semua Siswa dalam Kelompok Terpilih</span>
                        </label>
                        <div id="student_checkboxes_container"
                            class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                            <!-- Opsi siswa akan diisi oleh JavaScript -->
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Pilih satu atau lebih siswa.</p>
                </div>
                <!-- END: Tambahan untuk pemilihan siswa dengan checkboxes -->

                <div class="mb-4">
                    <label for="deadline_date" class="block text-gray-700 text-sm font-bold mb-2">Tanggal Tenggat
                        Waktu:</label>
                    <input type="date" id="deadline_date" name="deadline_date"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                </div>
                <div class="mb-4">
                    <label for="deadline_time" class="block text-gray-700 text-sm font-bold mb-2">Waktu Tenggat
                        Waktu:</label>
                    <input type="time" id="deadline_time" name="deadline_time"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                </div>
            </div>

            <h3 class="text-xl font-semibold text-gray-800 mb-4">Daftar Soal</h3>
            <div id="questions-container">
                <!-- Soal akan ditambahkan di sini oleh JavaScript -->
            </div>

            <button type="button" id="addQuestionBtn"
                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105 mb-6">
                Tambah Soal
            </button>

            <div class="mt-6 text-center">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                    Simpan Tugas & Soal
                </button>
            </div>
        </form>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const questionsContainer = document.getElementById('questions-container');
            const addQuestionBtn = document.getElementById('addQuestionBtn');
            let questionIndex = 0; // Global index for new questions

            const classGradeSelect = document.getElementById('class_grade');
            const groupCheckboxesContainer = document.getElementById('group_checkboxes_container');
            const selectAllGroupsCheckbox = document.getElementById('select_all_groups');

            const actualStudentCheckboxesContainer = document.getElementById('student_checkboxes_container');
            const selectAllStudentsCheckbox = document.getElementById('select_all_students');

            // Dapatkan semua kelompok dari PHP, pastikan ini sudah eager loaded dengan siswa-siswanya
            const allGroups = @json($groups);

            // Fungsi untuk merender checkboxes kelompok berdasarkan kelas yang difilter
            function renderGroupCheckboxes(filteredGroups) {
                groupCheckboxesContainer.innerHTML = ''; // Kosongkan dulu
                filteredGroups.forEach(group => {
                    const checkboxDiv = document.createElement('div');
                    checkboxDiv.innerHTML = `
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="group_ids[]" value="${group.id}" class="form-checkbox text-green-600 group-checkbox">
                            <span class="ml-2 text-gray-700">${group.name}</span>
                        </label>
                    `;
                    groupCheckboxesContainer.appendChild(checkboxDiv);
                });
            }

            // Fungsi utama untuk memfilter kelompok dan merender ulang
            function filterGroupsAndRender() {
                const selectedClass = classGradeSelect.value;
                selectAllGroupsCheckbox.checked = false; // Reset "Semua Kelompok"

                if (selectedClass) {
                    const filteredGroups = allGroups.filter(group => group.class_grade == selectedClass);
                    renderGroupCheckboxes(filteredGroups);
                } else {
                    groupCheckboxesContainer.innerHTML = ''; // Kosongkan jika tidak ada kelas terpilih
                }
                updateStudentList(); // Perbarui daftar siswa juga
            }

            // Event listener untuk "Semua Kelompok" checkbox
            selectAllGroupsCheckbox.addEventListener('change', function () {
                const isChecked = this.checked;
                document.querySelectorAll('.group-checkbox').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
                updateStudentList(); // Perbarui daftar siswa saat "Semua Kelompok" diubah
            });

            // Event listener delegasi untuk checkbox kelompok
            groupCheckboxesContainer.addEventListener('change', function (event) {
                if (event.target.classList.contains('group-checkbox')) {
                    // Jika ada perubahan pada checkbox kelompok, perbarui daftar siswa
                    updateStudentList();

                    // Cek apakah semua kelompok terpilih, update checkbox "Semua Kelompok"
                    const allGroupCheckboxes = document.querySelectorAll('.group-checkbox');
                    const allGroupsChecked = Array.from(allGroupCheckboxes).every(cb => cb.checked);
                    selectAllGroupsCheckbox.checked = allGroupsChecked;
                }
            });

            // Panggil filterGroupsAndRender saat halaman dimuat dan saat class_grade berubah
            classGradeSelect.addEventListener('change', filterGroupsAndRender);
            filterGroupsAndRender(); // Panggil saat awal untuk mengisi opsi berdasarkan default/kosong

            // Fungsi untuk memperbarui daftar siswa berdasarkan kelompok yang dipilih
            function updateStudentList() {
                actualStudentCheckboxesContainer.innerHTML = ''; // Kosongkan dulu
                const selectedGroupIds = Array.from(document.querySelectorAll('.group-checkbox:checked')).map(cb => cb.value);
                selectAllStudentsCheckbox.checked = false; // Reset "Semua Siswa"

                let studentsToShow = [];
                if (selectedGroupIds.length === 0) {
                    // Jika tidak ada kelompok terpilih, tampilkan semua siswa di kelas yang terpilih (jika ada)
                    const selectedClass = classGradeSelect.value;
                    if (selectedClass) {
                        const groupsInSelectedClass = allGroups.filter(group => group.class_grade == selectedClass);
                        groupsInSelectedClass.forEach(group => {
                            studentsToShow = studentsToShow.concat(group.students);
                        });
                    }
                } else {
                    // Tampilkan siswa dari kelompok yang dipilih
                    selectedGroupIds.forEach(groupId => {
                        const group = allGroups.find(g => g.id == groupId);
                        if (group) {
                            studentsToShow = studentsToShow.concat(group.students);
                        }
                    });
                }

                // Hapus duplikat siswa jika ada (misal, satu siswa di beberapa kelompok - jarang terjadi)
                const uniqueStudents = Array.from(new Set(studentsToShow.map(s => s.id)))
                    .map(id => studentsToShow.find(s => s.id === id));

                uniqueStudents.sort((a, b) => a.name.localeCompare(b.name)); // Urutkan berdasarkan nama

                uniqueStudents.forEach(student => {
                    const checkboxDiv = document.createElement('div');
                    checkboxDiv.innerHTML = `
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="student_ids[]" value="${student.id}" class="form-checkbox text-purple-600 student-checkbox">
                            <span class="ml-2 text-gray-700">${student.name}</span>
                        </label>
                    `;
                    actualStudentCheckboxesContainer.appendChild(checkboxDiv);
                });
            }

            // Event listener untuk "Semua Siswa" checkbox
            selectAllStudentsCheckbox.addEventListener('change', function () {
                const isChecked = this.checked;
                document.querySelectorAll('.student-checkbox').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            });

            // Event listener delegasi untuk checkbox siswa
            actualStudentCheckboxesContainer.addEventListener('change', function (event) {
                if (event.target.classList.contains('student-checkbox')) {
                    // Cek apakah semua siswa terpilih, update checkbox "Semua Siswa"
                    const allStudentCheckboxes = document.querySelectorAll('.student-checkbox');
                    const allStudentsChecked = Array.from(allStudentCheckboxes).every(cb => cb.checked);
                    selectAllStudentsCheckbox.checked = allStudentsChecked;
                }
            });

            // Fungsi untuk menambahkan blok soal baru
            function addQuestionBlock() {
                const currentQIndex = questionIndex; // Use current global index for this new block
                const questionBlock = document.createElement('div');
                questionBlock.classList.add('question-block');
                questionBlock.dataset.index = currentQIndex; // Set data-index for identification

                questionBlock.innerHTML = `
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold text-gray-800">Soal ${currentQIndex + 1}</h4>
                        <button type="button" class="remove-question-btn bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-lg text-sm transition duration-300 ease-in-out">
                            Hapus
                        </button>
                    </div>

                    <div class="mb-4">
                        <label for="question_type_${currentQIndex}" class="block text-gray-700 text-sm font-bold mb-2">Tipe Soal:</label>
                        <select id="question_type_${currentQIndex}" name="questions[${currentQIndex}][type]" class="question-type-select shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            <option value="">Pilih Tipe Soal</option>
                            <option value="multiple_choice">Pilihan Ganda</option>
                            <option value="essay">Esai</option>
                            <option value="true_false">Benar/Salah</option>
                            <option value="matching">Menjodohkan</option>
                            <option value="image_input">Input Gambar</option>
                        </select>
                    </div>

                  <div class="mb-4">
                        <label for="question_text_${currentQIndex}" class="block text-gray-700 text-sm font-bold mb-2">Konten Soal:</label>
                        <textarea id="question_text_${currentQIndex}" name="questions[${currentQIndex}][question_text]" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="question_score_${currentQIndex}" class="block text-gray-700 text-sm font-bold mb-2">Nilai Soal:</label>
                        <input type="number" id="question_score_${currentQIndex}" name="questions[${currentQIndex}][score]" min="1" max="100" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div class="mb-4 media-upload-section">
                        <label for="question_media_${currentQIndex}" class="block text-gray-700 text-sm font-bold mb-2">Media Soal (Opsional, Max 10MB):</label>
                        <input type="file" id="question_media_${currentQIndex}" name="questions[${currentQIndex}][media]" accept="image/*,video/*,.pdf" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <p class="text-xs text-gray-500 mt-1">Format: Gambar (jpg, png, gif), Video (mp4, webm, ogg), PDF</p>
                    </div>

                    <div class="question-options-area mt-4">
                        <!-- Opsi spesifik tipe soal akan dimuat di sini -->
                    </div>
                `;

                questionsContainer.appendChild(questionBlock);

                // Add event listener for question type change
                questionBlock.querySelector('.question-type-select').addEventListener('change', function () {
                    // When type changes, we don't have initialData, so pass null
                    updateQuestionOptions(this, currentQIndex);
                });

                // Add event listener for remove button
                questionBlock.querySelector('.remove-question-btn').addEventListener('click', function () {
                    questionBlock.remove();
                    reindexQuestions(); // Reindex after removal
                });

                questionIndex++; // Increment global index for the next new block
            }

            // Function to update question options based on type
            // initialData is used when re-rendering existing data (e.g., after reindexing)
            function updateQuestionOptions(selectElement, qIdx, initialData = null) {
                const optionsArea = selectElement.closest('.question-block').querySelector('.question-options-area');
                const questionType = selectElement.value;
                optionsArea.innerHTML = ''; // Clear previous options

                if (questionType === 'multiple_choice') {
                    optionsArea.innerHTML = `
                        <h5 class="text-md font-semibold text-gray-700 mb-3">Pilihan Ganda:</h5>
                        <div class="mb-2">
                            <label for="option_a_${qIdx}" class="block text-gray-700 text-sm font-bold mb-1">Opsi A:</label>
                            <input type="text" id="option_a_${qIdx}" name="questions[${qIdx}][options][a]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required value="${initialData && initialData.options && initialData.options.a ? initialData.options.a : ''}">
                        </div>
                        <div class="mb-2">
                            <label for="option_b_${qIdx}" class="block text-gray-700 text-sm font-bold mb-1">Opsi B:</label>
                            <input type="text" id="option_b_${qIdx}" name="questions[${qIdx}][options][b]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required value="${initialData && initialData.options && initialData.options.b ? initialData.options.b : ''}">
                        </div>
                        <div class="mb-2">
                            <label for="option_c_${qIdx}" class="block text-gray-700 text-sm font-bold mb-1">Opsi C:</label>
                            <input type="text" id="option_c_${qIdx}" name="questions[${qIdx}][options][c]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required value="${initialData && initialData.options && initialData.options.c ? initialData.options.c : ''}">
                        </div>
                        <div class="mb-2">
                            <label for="option_d_${qIdx}" class="block text-gray-700 text-sm font-bold mb-1">Opsi D:</label>
                            <input type="text" id="option_d_${qIdx}" name="questions[${qIdx}][options][d]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required value="${initialData && initialData.options && initialData.options.d ? initialData.options.d : ''}">
                        </div>
                        <div class="mb-2">
                            <label for="correct_answer_mc_${qIdx}" class="block text-gray-700 text-sm font-bold mb-1">Jawaban Benar:</label>
                            <select id="correct_answer_mc_${qIdx}" name="questions[${qIdx}][correct_answer]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                <option value="">Pilih Jawaban Benar</option>
                                <option value="a" ${initialData && initialData.correct_answer === 'a' ? 'selected' : ''}>A</option>
                                <option value="b" ${initialData && initialData.correct_answer === 'b' ? 'selected' : ''}>B</option>
                                <option value="c" ${initialData && initialData.correct_answer === 'c' ? 'selected' : ''}>C</option>
                                <option value="d" ${initialData && initialData.correct_answer === 'd' ? 'selected' : ''}>D</option>
                            </select>
                        </div>
                    `;
                } else if (questionType === 'essay') {
                    optionsArea.innerHTML = `
                        <h5 class="text-md font-semibold text-gray-700 mb-3">Kunci Jawaban Esai (Opsional, untuk referensi):</h5>
                        <textarea name="questions[${qIdx}][correct_answer]" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Masukkan kunci jawaban atau panduan penilaian esai" required>${initialData && initialData.correct_answer ? initialData.correct_answer : ''}</textarea>
                    `;
                } else if (questionType === 'true_false') {
                    optionsArea.innerHTML = `
                        <h5 class="text-md font-semibold text-gray-700 mb-3">Jawaban Benar/Salah:</h5>
                        <div class="mb-2">
                            <label class="inline-flex items-center mr-4">
                                <input type="radio" name="questions[${qIdx}][correct_answer]" value="true" class="form-radio text-blue-600" ${initialData && initialData.correct_answer === 'true' ? 'checked' : ''} required>
                                <span class="ml-2 text-gray-700">Benar</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="questions[${qIdx}][correct_answer]" value="false" class="form-radio text-blue-600" ${initialData && initialData.correct_answer === 'false' ? 'checked' : ''} required>
                                <span class="ml-2 text-gray-700">Salah</span>
                            </label>
                        </div>
                    `;
                } else if (questionType === 'matching') {
                    optionsArea.innerHTML = `
                        <h5 class="text-md font-semibold text-gray-700 mb-3">Pasangan Menjodohkan:</h5>
                        <div id="matching-pairs-container-${qIdx}">
                            <!-- Pasangan akan ditambahkan di sini -->
                        </div>
                        <button type="button" class="add-matching-pair-btn bg-purple-500 hover:bg-purple-600 text-white font-bold py-1 px-3 rounded-lg text-sm transition duration-300 ease-in-out mt-2">
                            Tambah Pasangan
                        </button>
                    `;
                    // Add event listener for "Tambah Pasangan" button
                    optionsArea.querySelector('.add-matching-pair-btn').addEventListener('click', function () {
                        addMatchingPair(qIdx);
                    });

                    // Reload existing pairs if initialData is provided
                    if (initialData && initialData.matching_pairs && initialData.matching_pairs.length > 0) {
                        initialData.matching_pairs.forEach(pair => addMatchingPair(qIdx, pair));
                    } else {
                        addMatchingPair(qIdx); // Add one pair by default
                    }
                } else if (questionType === 'image_input') {
                    optionsArea.innerHTML = `
                        <h5 class="text-md font-semibold text-gray-700 mb-3">Instruksi untuk Siswa (Opsional):</h5>
                        <textarea name="questions[${qIdx}][instructions]" rows="2" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Contoh: Gambarlah diagram proses fotosintesis.">${initialData && initialData.instructions ? initialData.instructions : ''}</textarea>
                    `;
                }
            }

            // Function to add a matching pair
            function addMatchingPair(qIdx, initialPair = null) {
                const matchingPairsContainer = document.getElementById(`matching-pairs-container-${qIdx}`);
                const pairIndex = matchingPairsContainer.children.length; // Get current number of children as index

                const pairDiv = document.createElement('div');
                pairDiv.classList.add('grid', 'grid-cols-2', 'gap-2', 'mb-2', 'items-center');
                pairDiv.innerHTML = `
                    <div>
                        <input type="text" name="questions[${qIdx}][matching_pairs][${pairIndex}][left]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Kolom Kiri" required value="${initialPair && initialPair.left ? initialPair.left : ''}">
                    </div>
                    <div class="flex items-center">
                        <input type="text" name="questions[${qIdx}][matching_pairs][${pairIndex}][right]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Kolom Kanan" required value="${initialPair && initialPair.right ? initialPair.right : ''}">
                        <button type="button" class="remove-matching-pair-btn bg-red-400 hover:bg-red-500 text-white font-bold py-1 px-2 rounded-lg text-xs ml-2">X</button>
                    </div>
                `;
                matchingPairsContainer.appendChild(pairDiv);

                // Add event listener for remove pair button
                pairDiv.querySelector('.remove-matching-pair-btn').addEventListener('click', function () {
                    pairDiv.remove();
                    reindexMatchingPairs(qIdx); // Reindex pairs after removal
                });
            }

            // Function to reindex matching pairs within a specific question block
            function reindexMatchingPairs(qIdx) {
                const matchingPairsContainer = document.getElementById(`matching-pairs-container-${qIdx}`);
                Array.from(matchingPairsContainer.children).forEach((pairDiv, pairNewIndex) => {
                    pairDiv.querySelectorAll('input').forEach(input => {
                        const oldName = input.name;
                        const newName = oldName.replace(/matching_pairs\[\d+\]/, `matching_pairs[${pairNewIndex}]`);
                        input.name = newName;
                        // Specific check for question_text vs content
                        if (oldName.includes('[content]')) {
                            input.name = input.name.replace('[content]', '[question_text]');
                        }
                    });
                });
            }

            // Function to reindex all question blocks after removal
            function reindexQuestions() {
                const questionBlocks = questionsContainer.querySelectorAll('.question-block');
                let newQuestionIndex = 0; // Use a local index for reindexing

                questionBlocks.forEach((block) => {
                    const oldIndex = block.dataset.index; // Get the old index
                    const currentBlockIndex = newQuestionIndex; // Assign new sequential index
                    block.dataset.index = currentBlockIndex; // Update data-index

                    block.querySelector('h4').textContent = `Soal ${currentBlockIndex + 1}`; // Update title

                    // Update name attributes for all inputs within the block
                    block.querySelectorAll('[name^="questions["]').forEach(input => {
                        const oldName = input.name;
                        // Replace the old index with the new index
                        const newName = oldName.replace(/questions\[\d+\]/, `questions[${currentBlockIndex}]`);
                        input.name = newName;
                    });

                    // Update id attributes for all inputs within the block
                    block.querySelectorAll('[id^="question_"]').forEach(input => {
                        const oldId = input.id;
                        // Replace the old index with the new index
                        const newId = oldId.replace(/_\d+/, `_${currentBlockIndex}`);
                        input.id = newId;
                        // Also update 'for' attributes of labels
                        const labelFor = document.querySelector(`label[for="${oldId}"]`);
                        if (labelFor) {
                            labelFor.setAttribute('for', newId);
                        }
                    });

                    // Update id for multiple choice options
                    block.querySelectorAll('[id^="option_"]').forEach(input => {
                        const oldId = input.id;
                        const newId = oldId.replace(/_\d+/, `_${currentBlockIndex}`);
                        input.id = newId;
                        const labelFor = document.querySelector(`label[for="${oldId}"]`);
                        if (labelFor) {
                            labelFor.setAttribute('for', newId);
                        }
                    });

                    // Update id for multiple choice correct answer select
                    block.querySelectorAll('[id^="correct_answer_mc_"]').forEach(select => {
                        const oldId = select.id;
                        const newId = oldId.replace(/_\d+/, `_${currentBlockIndex}`);
                        select.id = newId;
                        const labelFor = document.querySelector(`label[for="${oldId}"]`);
                        if (labelFor) {
                            labelFor.setAttribute('for', newId);
                        }
                    });

                    // Update id for matching pairs container
                    block.querySelectorAll('[id^="matching-pairs-container-"]').forEach(container => {
                        const oldId = container.id;
                        const newId = oldId.replace(/-\d+/, `-${currentBlockIndex}`);
                        container.id = newId;
                        reindexMatchingPairs(currentBlockIndex); // Reindex pairs within this container
                    });

                    newQuestionIndex++; // Increment local index for the next block
                });
                questionIndex = newQuestionIndex; // Update global index after reindexing
            }

            // Add event listener for "Tambah Soal" button
            addQuestionBtn.addEventListener('click', addQuestionBlock);

            // Add one question block by default when the page loads
            addQuestionBlock();
        });
    </script>
</body>

</html>