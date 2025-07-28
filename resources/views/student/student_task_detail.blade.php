<!-- resources/views/student/student_task_detail.blade.php -->
@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Tugas: {{ $task->title }}</h1>
    <p class="text-center text-gray-600 mb-4">Kelas: {{ $task->class_grade }} | Kelompok: {{ $task->group ? $task->group->name : 'Umum' }}</p>
    <p class="text-center text-gray-600 mb-6">Tenggat Waktu: <span id="task-deadline-countdown" class="font-semibold text-blue-700"></span></p>

    <div class="container mx-auto p-6 mt-8 bg-white rounded-xl shadow-lg">
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

        @if($submission && $submission->is_completed)
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
                <p class="font-bold">Anda sudah mengumpulkan tugas ini.</p>
                <p>Nilai Anda: {{ $submission->score ?? 'Belum Dinilai' }}</p>
                <p>Umpan Balik: {{ $submission->feedback ?? '-' }}</p>
                <p class="text-sm mt-2">Anda dapat melihat jawaban yang telah Anda kirimkan di bawah ini.</p>
            </div>
        @endif

        <form id="taskSubmissionForm" action="{{ route('student.task.submit', $task->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- Input hidden untuk student_id --}}
            <input type="hidden" name="student_id" value="{{ $studentId }}">

            @forelse($task->questions as $index => $question)
                <div class="question-block border border-gray-200 p-6 rounded-md shadow-sm bg-white mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-3">Soal {{ $index + 1 }}.</h4>
                    <p class="text-gray-700 mb-4">{{ $question->content }}</p>

                    @if($question->media_path)
                        @php
                            $extension = pathinfo($question->media_path, PATHINFO_EXTENSION);
                        @endphp
                        @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                            <img src="{{ asset('storage/' . $question->media_path) }}" alt="Media Soal" class="max-w-full h-auto rounded-md mb-4">
                        @elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
                            <video controls class="max-w-full h-auto rounded-md mb-4">
                                <source src="{{ asset('storage/' . $question->media_path) }}" type="video/{{ $extension }}">
                                Browser Anda tidak mendukung tag video.
                            </video>
                        @endif
                    @endif

                    <div class="options-area mt-4">
                        @if($question->type === 'multiple_choice')
                            @php $options = json_decode($question->options, true); @endphp
                            @foreach($options as $key => $value)
                                <div class="mb-2">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="answers[{{ $index }}][student_answer]" value="{{ $key }}" class="form-radio text-blue-600"
                                            {{ ($submission && isset(json_decode($submission->answers, true)[$question->id]) && json_decode($submission->answers, true)[$question->id] == $key) ? 'checked' : '' }}
                                            {{ ($submission && $submission->is_completed) ? 'disabled' : '' }}>
                                        <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question->id }}">
                                        <span class="ml-2 text-gray-700">{{ strtoupper($key) }}. {{ $value }}</span>
                                    </label>
                                </div>
                            @endforeach
                        @elseif($question->type === 'essay')
                            <label for="essay_answer_{{ $question->id }}" class="block text-gray-700 text-sm font-bold mb-2">Jawaban Anda:</label>
                           <textarea id="essay_answer_{{ $question->id }}" name="answers[{{ $index }}][student_answer]" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                {{ ($submission && $submission->is_completed) ? 'disabled' : '' }}>{{ ($submission && isset(json_decode($submission->answers, true)[$question->id])) ? json_decode($submission->answers, true)[$question->id] : '' }}</textarea>
                            <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question->id }}">
                        @elseif($question->type === 'true_false')
                            <div class="mb-2">
                                <label class="inline-flex items-center mr-4">
                                    <input type="radio" name="answers[{{ $index }}][student_answer]" value="true" class="form-radio text-blue-600"
                                        {{ ($submission && isset(json_decode($submission->answers, true)[$question->id]) && json_decode($submission->answers, true)[$question->id] == 'true') ? 'checked' : '' }}
                                        {{ ($submission && $submission->is_completed) ? 'disabled' : '' }}>
                                    <span class="ml-2 text-gray-700">Benar</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="answers[{{ $index }}][student_answer]" value="false" class="form-radio text-blue-600"
                                        {{ ($submission && isset(json_decode($submission->answers, true)[$question->id]) && json_decode($submission->answers, true)[$question->id] == 'false') ? 'checked' : '' }}
                                        {{ ($submission && $submission->is_completed) ? 'disabled' : '' }}>
                                    <span class="ml-2 text-gray-700">Salah</span>
                                </label>
                            </div>
                            <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question->id }}">
                        @elseif($question->type === 'matching')
                            @php $matchingPairs = json_decode($question->options, true); @endphp
                            <h5 class="text-md font-semibold text-gray-700 mb-3">Jodohkan Pasangan:</h5>
                            <div class="grid grid-cols-2 gap-4">
                                @foreach($matchingPairs as $idx => $pair)
                                    <div class="flex flex-col mb-2">
                                        <label class="block text-gray-700 text-sm font-bold mb-1">{{ $pair['left'] }}</label>
                                        <input type="text" name="answers[{{ $index }}][student_answer][{{ $idx }}][right]"
                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                            placeholder="Jodohkan dengan..."
                                            value="{{ ($submission && isset(json_decode($submission->answers, true)[$question->id][$idx]['right'])) ? json_decode($submission->answers, true)[$question->id][$idx]['right'] : '' }}"
                                            {{ ($submission && $submission->is_completed) ? 'disabled' : '' }}>
                                        <input type="hidden" name="answers[{{ $index }}][student_answer][{{ $idx }}][left]" value="{{ $pair['left'] }}">
                                        <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question->id }}">
                                    </div>
                                @endforeach
                            </div>
                        @elseif($question->type === 'image_input')
                            <h5 class="text-md font-semibold text-gray-700 mb-3">Instruksi: {{ json_decode($question->options) }}</h5>
                            <label for="image_answer_{{ $question->id }}" class="block text-gray-700 text-sm font-bold mb-2">Unggah Gambar Jawaban Anda:</label>
                           <input type="file" id="image_answer_{{ $question->id }}" name="answers[{{ $index }}][student_answer]" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                {{ ($submission && $submission->is_completed) ? 'disabled' : '' }}>
                            <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question->id }}">
                            @if($submission && isset(json_decode($submission->answers, true)[$question->id]))
                                <p class="text-sm text-gray-600 mt-2">Gambar yang sudah diunggah: <a href="{{ asset('storage/' . json_decode($submission->answers, true)[$question->id]) }}" target="_blank" class="text-blue-500 hover:underline">{{ basename(json_decode($submission->answers, true)[$question->id]) }}</a></p>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center">Tidak ada soal untuk tugas ini.</p>
            @endforelse

            <div class="mt-6 text-center">
                @if(!$submission || !$submission->is_completed)
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                        Kumpulkan Tugas
                    </button>
                @else
                    <button type="button" class="bg-gray-400 text-white font-bold py-3 px-6 rounded-lg cursor-not-allowed">
                        Tugas Sudah Dikumpulkan
                    </button>
                @endif
            </div>
        </form>
    </div>

    <script>
        // Countdown untuk tenggat waktu tugas
        const deadlineElement = document.getElementById('task-deadline-countdown');
        const taskDeadline = new Date("{{ \Carbon\Carbon::parse($task->deadline)->toIso8601String() }}").getTime();

        function updateTaskCountdown() {
            const now = new Date().getTime();
            const distance = taskDeadline - now;

            if (distance < 0) {
                deadlineElement.innerHTML = "Tenggat waktu habis!";
                deadlineElement.classList.remove('text-blue-700', 'text-yellow-700');
                deadlineElement.classList.add('text-red-700');
                document.getElementById('taskSubmissionForm').querySelectorAll('input, textarea, select, button').forEach(el => {
                    if (el.type !== 'submit') el.disabled = true; // Nonaktifkan semua input kecuali tombol submit
                });
                document.querySelector('button[type="submit"]').disabled = true; // Nonaktifkan tombol submit
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            deadlineElement.innerHTML = `${days}h ${hours}j ${minutes}m ${seconds}d`;

            if (days < 1 && hours < 24) {
                deadlineElement.classList.remove('text-blue-700');
                deadlineElement.classList.add('text-yellow-700');
            } else {
                deadlineElement.classList.remove('text-yellow-700', 'text-red-700');
                deadlineElement.classList.add('text-blue-700');
            }
        }

        // Panggil sekali saat halaman dimuat
        updateTaskCountdown();
        // Panggil setiap detik
        setInterval(updateTaskCountdown, 1000);

        // Logika untuk menangani pengiriman form dengan file
        document.getElementById('taskSubmissionForm').addEventListener('submit', async function(event) {
            event.preventDefault(); // Mencegah pengiriman form default

            const form = event.target;
            const formData = new FormData(form);

            // Tambahkan jawaban untuk input gambar secara manual jika ada
            document.querySelectorAll('input[type="file"][name^="answers"]').forEach(fileInput => {
                if (fileInput.files.length > 0) {
                    // Nama input file adalah answers[question_id], kita perlu mengekstrak question_id
                    const name = fileInput.name; // Contoh: answers[123]
                    const questionId = name.match(/\[(.*?)\]/)[1]; // Ekstrak 123
                    formData.append(`answers[${questionId}]`, fileInput.files[0]);
                }
            });

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData, // FormData akan otomatis mengatur Content-Type: multipart/form-data
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.reload(); // Reload halaman untuk menampilkan status terbaru
                } else {
                    alert('Gagal mengumpulkan tugas: ' + (result.message || 'Terjadi kesalahan.'));
                    if (result.errors) {
                        // Tampilkan error validasi dari backend jika ada
                        let errorMessages = '';
                        for (const key in result.errors) {
                            errorMessages += result.errors[key].join('\n') + '\n';
                        }
                        alert('Error validasi:\n' + errorMessages);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengumpulkan tugas.');
            }
        });

        // Logika untuk menyimpan student_id ke sessionStorage saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const studentIdInput = document.querySelector('input[name="student_id"]');
            if (studentIdInput && studentIdInput.value) {
                sessionStorage.setItem('current_student_id', studentIdInput.value);
            }
        });

    </script>
@endsection
