<!-- resources/views/student/student_task_detail.blade.php -->
@extends('layouts.app')

@section('content')
    <div id="quiz-container" class="min-h-screen p-4 sm:p-6 md:p-8 flex flex-col items-center justify-center transition-colors duration-500">
        <div class="w-full max-w-4xl bg-white rounded-xl shadow-2xl p-6 sm:p-8 md:p-10 relative z-10">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-center text-gray-800 mb-4">{{ $task->title }}</h1>
            <p class="text-center text-gray-600 mb-2 text-sm sm:text-base">Kelas: {{ $task->class_grade }} | Kelompok: {{ $task->group ? $task->group->name : 'Umum' }}</p>
            <p class="text-center text-gray-600 mb-6 text-sm sm:text-base">Tenggat Waktu: <span id="task-deadline-countdown" class="font-semibold text-blue-700"></span></p>

            {{-- Success/error messages --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                    <strong class="font-bold">Sukses!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                    <strong class="font-bold">Error Validasi!</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($submission && $submission->is_completed)
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded-lg" role="alert">
                    <p class="font-bold">Anda sudah mengumpulkan tugas ini.</p>
                    <p>Nilai Anda: {{ $submission->score ?? 'Belum Dinilai' }}</p>
                    <p>Umpan Balik: {{ $submission->feedback ?? '-' }}</p>
                    <p class="text-sm mt-2">Anda dapat melihat jawaban yang telah Anda kirimkan di bawah ini.</p>
                </div>
            @endif

            <form id="taskSubmissionForm" action="{{ route('student.task.submit', $task->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                {{-- Hidden input for student_id --}}
                <input type="hidden" name="student_id" value="{{ $studentId }}">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Main Question Card --}}
                    <div class="md:col-span-2 bg-gray-50 p-6 rounded-xl shadow-inner border border-gray-200">
                        @forelse($task->questions as $index => $question)
                            <div class="question-block {{ $index === 0 ? '' : 'hidden' }}" data-question-index="{{ $index }}">
                                <div class="bg-white p-6 rounded-xl shadow-lg border border-blue-100 mb-6">
                                    <h4 class="text-xl font-bold text-gray-800 mb-4">Soal {{ $index + 1 }}.</h4>
                                    <p class="text-gray-700 text-lg mb-4">{{ $question->content }}</p>

                                    @if($question->media_path)
                                        @php
                                            $extension = pathinfo($question->media_path, PATHINFO_EXTENSION);
                                        @endphp
                                        @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                                            <img src="{{ asset('storage/' . $question->media_path) }}" alt="Media Soal" class="max-w-full h-auto rounded-lg mb-4 shadow-md">
                                        @elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
                                            <video controls class="max-w-full h-auto rounded-lg mb-4 shadow-md">
                                                <source src="{{ asset('storage/' . $question->media_path) }}" type="video/{{ $extension }}">
                                                Browser Anda tidak mendukung tag video.
                                            </video>
                                        @endif
                                    @endif

                                    <div class="options-area mt-4">
                                        {{-- Add hidden input for question_id for each question --}}
                                        <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question->id }}">

                                        @if($question->type === 'multiple_choice')
                                            @php
                                                $options = json_decode($question->options, true);
                                                $correctAnswer = json_decode($question->correct_answer, true);
                                                $studentAnswer = ($submission && isset(json_decode($submission->answers, true)[$question->id]['student_answer'])) ? json_decode($submission->answers, true)[$question->id]['student_answer'] : null;
                                            @endphp
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                @foreach($options as $key => $value)
                                                    @php
                                                        $optionClass = 'bg-blue-50 hover:bg-blue-100 border-blue-200'; // Default
                                                        if ($submission && $submission->is_completed) {
                                                            if ((string)$key === (string)$correctAnswer) {
                                                                $optionClass = 'bg-green-100 border-green-400'; // Correct answer
                                                            }
                                                            if ((string)$key === (string)$studentAnswer && (string)$key !== (string)$correctAnswer) {
                                                                $optionClass = 'bg-red-100 border-red-400'; // Student's wrong answer
                                                            } elseif ((string)$key === (string)$studentAnswer && (string)$key === (string)$correctAnswer) {
                                                                $optionClass = 'bg-green-100 border-green-400'; // Student's correct answer
                                                            }
                                                        }
                                                    @endphp
                                                    <label class="flex items-center p-4 rounded-lg cursor-pointer transition duration-200 ease-in-out border {{ $optionClass }}">
                                                        <input type="radio" name="answers[{{ $index }}][student_answer]" value="{{ $key }}" class="form-radio text-blue-600 h-5 w-5"
                                                            {{ ($studentAnswer && (string)$studentAnswer === (string)$key) ? 'checked' : '' }}
                                                            {{ ($submission && $submission->is_completed) ? 'disabled' : '' }}>
                                                        <span class="ml-3 text-gray-800 font-medium">{{ strtoupper($key) }}. {{ $value }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @elseif($question->type === 'essay')
                                            <label for="essay_answer_{{ $question->id }}" class="block text-gray-700 text-sm font-bold mb-2">Jawaban Anda:</label>
                                            @php
                                                $essayAnswer = '';
                                                if ($submission && isset(json_decode($submission->answers, true)[$question->id]['student_answer'])) {
                                                    $decodedAnswer = json_decode($submission->answers, true)[$question->id]['student_answer'];
                                                    $essayAnswer = is_array($decodedAnswer) ? implode(', ', $decodedAnswer) : $decodedAnswer;
                                                }
                                                $correctEssayAnswer = json_decode($question->correct_answer, true);
                                            @endphp
                                           <textarea id="essay_answer_{{ $question->id }}" name="answers[{{ $index }}][student_answer]" rows="6" class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                {{ ($submission && $submission->is_completed) ? 'disabled' : '' }}>{{ $essayAnswer }}</textarea>

                                            @if($submission && $submission->is_completed && $correctEssayAnswer)
                                                <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                                                    <p class="font-bold mb-2">Jawaban Sebenarnya:</p>
                                                    <p>{{ $correctEssayAnswer }}</p>
                                                </div>
                                            @endif

                                        @elseif($question->type === 'true_false')
                                            @php
                                                $correctAnswer = json_decode($question->correct_answer, true);
                                                $studentAnswer = ($submission && isset(json_decode($submission->answers, true)[$question->id]['student_answer'])) ? json_decode($submission->answers, true)[$question->id]['student_answer'] : null;
                                            @endphp
                                            <div class="flex flex-col sm:flex-row gap-4">
                                                @php
                                                    $trueClass = 'bg-green-50 hover:bg-green-100 border-green-200';
                                                    $falseClass = 'bg-red-50 hover:bg-red-100 border-red-200';

                                                    if ($submission && $submission->is_completed) {
                                                        // Highlight correct answer
                                                        if ((string)$correctAnswer === 'true') {
                                                            $trueClass = 'bg-green-100 border-green-400';
                                                        } else {
                                                            $falseClass = 'bg-green-100 border-green-400';
                                                        }

                                                        // Highlight student's answer if wrong
                                                        if ((string)$studentAnswer !== (string)$correctAnswer) {
                                                            if ((string)$studentAnswer === 'true') {
                                                                $trueClass = 'bg-red-100 border-red-400';
                                                            } else {
                                                                $falseClass = 'bg-red-100 border-red-400';
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <label class="flex items-center p-4 rounded-lg cursor-pointer transition duration-200 ease-in-out border flex-1 {{ $trueClass }}">
                                                    <input type="radio" name="answers[{{ $index }}][student_answer]" value="true" class="form-radio text-green-600 h-5 w-5"
                                                        {{ ($studentAnswer && (string)$studentAnswer === 'true') ? 'checked' : '' }}
                                                        {{ ($submission && $submission->is_completed) ? 'disabled' : '' }}>
                                                    <span class="ml-3 text-gray-800 font-medium">Benar</span>
                                                </label>
                                                <label class="flex items-center p-4 rounded-lg cursor-pointer transition duration-200 ease-in-out border flex-1 {{ $falseClass }}">
                                                    <input type="radio" name="answers[{{ $index }}][student_answer]" value="false" class="form-radio text-red-600 h-5 w-5"
                                                        {{ ($studentAnswer && (string)$studentAnswer === 'false') ? 'checked' : '' }}
                                                        {{ ($submission && $submission->is_completed) ? 'disabled' : '' }}>
                                                    <span class="ml-3 text-gray-800 font-medium">Salah</span>
                                                </label>
                                            </div>
                                        @elseif($question->type === 'matching')
                                            @php
                                                $matchingPairs = json_decode($question->options, true);
                                                $studentAnswersMatching = ($submission && isset(json_decode($submission->answers, true)[$question->id]['student_answer'])) ? json_decode($submission->answers, true)[$question->id]['student_answer'] : [];
                                                $isQuestionCorrect = ($submission && isset(json_decode($submission->answers, true)[$question->id]['is_correct'])) ? json_decode($submission->answers, true)[$question->id]['is_correct'] : false;
                                            @endphp
                                            <h5 class="text-md font-semibold text-gray-700 mb-3">Jodohkan Pasangan:</h5>
                                            <div class="grid grid-cols-1 gap-4">
                                                @foreach($matchingPairs as $idx => $pair)
                                                    @php
                                                        $studentRightAnswer = $studentAnswersMatching[$idx]['right'] ?? '';
                                                        $isPairCorrect = false;
                                                        if ($submission && $submission->is_completed) {
                                                            $isPairCorrect = ((string)$studentRightAnswer === (string)$pair['right']);
                                                        }
                                                        $pairClass = 'bg-purple-50 border-purple-200';
                                                        if ($submission && $submission->is_completed) {
                                                            if ($isPairCorrect) {
                                                                $pairClass = 'bg-green-100 border-green-400';
                                                            } else {
                                                                $pairClass = 'bg-red-100 border-red-400';
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="flex flex-col sm:flex-row items-start sm:items-center p-4 rounded-lg border {{ $pairClass }}">
                                                        <label class="block text-gray-700 font-bold mb-1 sm:mb-0 sm:w-1/3">{{ $pair['left'] }}</label>
                                                        <input type="text" name="answers[{{ $index }}][student_answer][{{ $idx }}][right]"
                                                            class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full sm:w-2/3 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent mt-2 sm:mt-0"
                                                            placeholder="Jodohkan dengan..."
                                                            data-matching-index="{{ $idx }}"
                                                            value="{{ $studentRightAnswer }}"
                                                            {{ ($submission && $submission->is_completed) ? 'disabled' : '' }}>
                                                        <input type="hidden" name="answers[{{ $index }}][student_answer][{{ $idx }}][left]" value="{{ $pair['left'] }}">
                                                        @if($submission && $submission->is_completed && !$isPairCorrect)
                                                            <p class="text-xs text-green-700 mt-1 sm:ml-2">Jawaban benar: {{ $pair['right'] }}</p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif($question->type === 'image_input')
                                            @php
                                                $instructions = json_decode($question->options, true);
                                                $displayInstructions = is_array($instructions) ? implode(', ', $instructions) : $instructions;
                                                $studentImageAnswer = ($submission && isset(json_decode($submission->answers, true)[$question->id]['student_answer'])) ? json_decode($submission->answers, true)[$question->id]['student_answer'] : null;
                                            @endphp
                                            <h5 class="text-md font-semibold text-gray-700 mb-3">Instruksi: {{ htmlspecialchars($displayInstructions) }}</h5>
                                            <label for="image_answer_{{ $question->id }}" class="block text-gray-700 text-sm font-bold mb-2">Unggah Gambar Jawaban Anda:</label>
                                           <input type="file" id="image_answer_{{ $question->id }}" name="answers[{{ $index }}][student_answer_file]" accept="image/*" class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100"
                                                {{ ($submission && $submission->is_completed) ? 'disabled' : '' }}>
                                            {{-- Hidden input to carry existing image path if no new file is uploaded --}}
                                            @if($submission && $studentImageAnswer)
                                                <input type="hidden" name="answers[{{ $index }}][student_answer_existing_path]" value="{{ $studentImageAnswer }}">
                                                <p class="text-sm text-gray-600 mt-2">Gambar yang sudah diunggah: <a href="{{ asset('storage/' . $studentImageAnswer) }}" target="_blank" class="text-blue-500 hover:underline">{{ basename($studentImageAnswer) }}</a></p>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center text-lg">Tidak ada soal untuk tugas ini.</p>
                        @endforelse

                        {{-- Question Navigation (Prev/Next) --}}
                        <div class="flex justify-between mt-6">
                            <button type="button" id="prev-question" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-full transition duration-300 ease-in-out transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                                &larr; Sebelumnya
                            </button>
                            <button type="button" id="next-question" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full transition duration-300 ease-in-out transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                                Selanjutnya &rarr;
                            </button>
                        </div>
                    </div>

                    {{-- Question Order Card --}}
                    <div class="md:col-span-1 bg-gray-50 p-6 rounded-xl shadow-inner border border-gray-200">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Urutan Soal</h3>
                        <div id="question-navigation" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach($task->questions as $index => $question)
                                @php
                                    $navItemClass = 'bg-gray-200 hover:bg-gray-300 text-gray-800';
                                    if ($index === 0) {
                                        $navItemClass = 'bg-blue-600 text-white hover:bg-blue-700';
                                    }
                                    // Check if question was answered and if it was correct/incorrect
                                    if ($submission && $submission->is_completed && isset(json_decode($submission->answers, true)[$question->id])) {
                                        $answerData = json_decode($submission->answers, true)[$question->id];
                                        if (isset($answerData['is_correct']) && $answerData['is_correct']) {
                                            $navItemClass = 'bg-green-500 text-white hover:bg-green-600';
                                        } elseif (isset($answerData['is_correct']) && !$answerData['is_correct']) {
                                            $navItemClass = 'bg-red-500 text-white hover:bg-red-600';
                                        }
                                    }
                                @endphp
                                <button type="button" class="question-nav-item font-semibold py-2 rounded-lg transition duration-200 ease-in-out text-sm {{ $navItemClass }}"
                                    data-question-index="{{ $index }}">
                                    {{ $index + 1 }}
                                </button>
                            @endforeach
                        </div>

                        {{-- Submit Task Button --}}
                        <div id="submit-task-container" class="mt-8 text-center hidden">
                            @if(!$submission || !$submission->is_completed)
                                <button type="submit" class="bg-gradient-to-r from-green-500 to-green-700 hover:from-green-600 hover:to-green-800 text-white font-bold py-3 px-6 rounded-full shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
                                    Kumpulkan Tugas
                                </button>
                            @else
                                <button type="button" class="bg-gray-400 text-white font-bold py-3 px-6 rounded-full cursor-not-allowed shadow-md">
                                    Tugas Sudah Dikumpulkan
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Function to change background color randomly
        function setRandomBackground() {
            const colors = [
                'bg-blue-200', 'bg-green-200', 'bg-purple-200', 'bg-yellow-200', 'bg-pink-200',
                'bg-indigo-200', 'bg-red-200', 'bg-teal-200', 'bg-orange-200', 'bg-cyan-200'
            ];
            const randomColor = colors[Math.floor(Math.random() * colors.length)];
            const container = document.getElementById('quiz-container');
            // Remove all existing color classes before adding a new one
            container.classList.remove(...colors);
            container.classList.add(randomColor);
        }

        // Call function when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            setRandomBackground();
            initializeQuizLogic();
            // Save student_id to sessionStorage when page is loaded
            const studentIdInput = document.querySelector('input[name="student_id"]');
            if (studentIdInput && studentIdInput.value) {
                sessionStorage.setItem('current_student_id', studentIdInput.value);
            }
        });

        let currentQuestionIndex = 0;
        const questionBlocks = document.querySelectorAll('.question-block');
        const navItems = document.querySelectorAll('.question-nav-item');
        const prevButton = document.getElementById('prev-question');
        const nextButton = document.getElementById('next-question');
        const submitButtonContainer = document.getElementById('submit-task-container');
        const totalQuestions = questionBlocks.length;

        // Object to track the answer status of each question
        const answeredQuestions = {};
        // Initialize answer status from existing data (if there was a previous submission)
        @if($submission && $submission->answers)
            @php
                $submittedAnswers = json_decode($submission->answers, true);
            @endphp
            @foreach($task->questions as $index => $question)
                @if(isset($submittedAnswers[$question->id]))
                    answeredQuestions[{{ $index }}] = true;
                @endif
            @endforeach
        @endif

        function showQuestion(index) {
            // Hide all question blocks
            questionBlocks.forEach(block => block.classList.add('hidden'));
            // Display the selected question block
            if (questionBlocks[index]) {
                questionBlocks[index].classList.remove('hidden');
            }

            // Update navigation button status
            prevButton.disabled = index === 0;
            nextButton.disabled = index === totalQuestions - 1;

            // Update question navigation item display
            navItems.forEach((item, idx) => {
                item.classList.remove('bg-blue-600', 'text-white', 'hover:bg-blue-700');
                item.classList.add('bg-gray-200', 'text-gray-800', 'hover:bg-gray-300');
                if (idx === index) {
                    item.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700');
                }
                // Mark answered questions
                if (answeredQuestions[idx]) {
                    // If already submitted, the color will be handled by Blade.
                    // Otherwise, mark as answered (green)
                    @if(!($submission && $submission->is_completed))
                        item.classList.remove('bg-gray-200', 'text-gray-800', 'hover:bg-gray-300');
                        item.classList.add('bg-green-500', 'text-white', 'hover:bg-green-600');
                    @endif
                }
            });

            // Check if the submit task button should be displayed
            checkAllQuestionsAnswered();
        }

        function checkAllQuestionsAnswered() {
            let allAnswered = true;
            for (let i = 0; i < totalQuestions; i++) {
                if (!answeredQuestions[i]) {
                    allAnswered = false;
                    break;
                }
            }
            if (allAnswered && totalQuestions > 0) {
                submitButtonContainer.classList.remove('hidden');
            } else {
                submitButtonContainer.classList.add('hidden');
            }
        }

        function initializeQuizLogic() {
            // Display the first question on initialization
            showQuestion(currentQuestionIndex);

            // Event listener for "Previous" button
            prevButton.addEventListener('click', () => {
                if (currentQuestionIndex > 0) {
                    currentQuestionIndex--;
                    showQuestion(currentQuestionIndex);
                }
            });

            // Event listener for "Next" button
            nextButton.addEventListener('click', () => {
                if (currentQuestionIndex < totalQuestions - 1) {
                    currentQuestionIndex++;
                    showQuestion(currentQuestionIndex);
                }
            });

            // Event listener for question navigation (question number)
            navItems.forEach(item => {
                item.addEventListener('click', () => {
                    const index = parseInt(item.dataset.questionIndex);
                    currentQuestionIndex = index;
                    showQuestion(currentQuestionIndex);
                });
            });

            // Event listener for answer input
            document.getElementById('taskSubmissionForm').addEventListener('change', (event) => {
                const target = event.target;
                const questionBlockElement = target.closest('.question-block');
                const questionId = questionBlockElement.querySelector('input[name^="answers"][name$="[question_id]"]').value;

                if (questionId) {
                    // Find question index based on questionId
                    let questionIndex = -1;
                    questionBlocks.forEach((block, idx) => {
                        const inputElement = block.querySelector(`input[name^="answers"][name$="[question_id]"]`);
                        if (inputElement && inputElement.value == questionId) {
                            questionIndex = idx;
                        }
                    });

                    if (questionIndex !== -1) {
                        let isAnswered = false;
                        // Improved logic to determine question type based on available inputs
                        let questionType = '';
                        if (questionBlockElement.querySelector('input[type="radio"][name^="answers"][value="true"], input[type="radio"][name^="answers"][value="false"]')) {
                            questionType = 'true_false';
                        } else if (questionBlockElement.querySelector('input[type="radio"][name^="answers"]')) {
                            questionType = 'multiple_choice';
                        } else if (questionBlockElement.querySelector('textarea[name^="answers"]')) {
                            questionType = 'essay';
                        } else if (questionBlockElement.querySelector('input[type="file"][name^="answers"]')) {
                            questionType = 'image_input';
                        } else if (questionBlockElement.querySelector('input[data-matching-index]')) {
                            questionType = 'matching';
                        }


                        if (questionType === 'multiple_choice' || questionType === 'true_false') {
                            isAnswered = target.checked;
                        } else if (questionType === 'essay') {
                            isAnswered = target.value.trim() !== '';
                        } else if (questionType === 'matching') {
                            const matchingInputs = questionBlockElement.querySelectorAll(`input[name^="answers[${questionIndex}]"][data-matching-index]`);
                            isAnswered = Array.from(matchingInputs).every(input => input.value.trim() !== '');
                        } else if (questionType === 'image_input') {
                            isAnswered = target.files.length > 0 || (questionBlockElement.querySelector(`input[type="hidden"][name="answers[${questionIndex}][student_answer_existing_path]"]`) && questionBlockElement.querySelector(`input[type="hidden"][name="answers[${questionIndex}][student_answer_existing_path]"]`).value.trim() !== '');
                        }

                        if (isAnswered) {
                            answeredQuestions[questionIndex] = true;
                            // Update question navigation button color
                            const navItem = document.querySelector(`.question-nav-item[data-question-index="${questionIndex}"]`);
                            if (navItem) {
                                @if(!($submission && $submission->is_completed))
                                    navItem.classList.remove('bg-gray-200', 'text-gray-800', 'hover:bg-gray-300', 'bg-blue-600', 'hover:bg-blue-700');
                                    navItem.classList.add('bg-green-500', 'text-white', 'hover:bg-green-600');
                                @endif
                            }
                        } else {
                            delete answeredQuestions[questionIndex]; // Remove if answer is cleared
                            const navItem = document.querySelector(`.question-nav-item[data-question-index="${questionIndex}"]`);
                            if (navItem) {
                                @if(!($submission && $submission->is_completed))
                                    navItem.classList.remove('bg-green-500', 'text-white', 'hover:bg-green-600');
                                    navItem.classList.add('bg-gray-200', 'text-gray-800', 'hover:bg-gray-300');
                                @endif
                            }
                        }
                        checkAllQuestionsAnswered();
                    }
                }
            });

            // Initialize answer status when page is loaded
            questionBlocks.forEach((block, index) => {
                const questionTypeElement = block.querySelector('.options-area');
                if (!questionTypeElement) return; // Skip if no options area found

                const radioInputs = questionTypeElement.querySelectorAll('input[type="radio"]');
                const textareaInput = questionTypeElement.querySelector('textarea');
                const fileInput = questionTypeElement.querySelector('input[type="file"]');
                const matchingInputs = questionTypeElement.querySelectorAll('input[data-matching-index]');

                let isAnswered = false;

                if (radioInputs.length > 0) {
                    isAnswered = Array.from(radioInputs).some(input => input.checked);
                } else if (textareaInput) {
                    isAnswered = textareaInput.value.trim() !== '';
                } else if (fileInput) {
                    const existingPathInput = fileInput.nextElementSibling; // Hidden input for existing path
                    isAnswered = fileInput.files.length > 0 || (existingPathInput && existingPathInput.name.includes('existing_path') && existingPathInput.value.trim() !== '');
                } else if (matchingInputs.length > 0) {
                    isAnswered = Array.from(matchingInputs).every(input => input.value.trim() !== '');
                }

                if (isAnswered) {
                    answeredQuestions[index] = true;
                    const navItem = document.querySelector(`.question-nav-item[data-question-index="${index}"]`);
                    if (navItem) {
                        @if(!($submission && $submission->is_completed))
                            navItem.classList.remove('bg-gray-200', 'text-gray-800', 'hover:bg-gray-300');
                            navItem.classList.add('bg-green-500', 'text-white', 'hover:bg-green-600');
                        @endif
                    }
                }
            });
            checkAllQuestionsAnswered(); // Call for the first time
        }


        // Countdown for task deadline (remains the same)
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
                    if (el.type !== 'submit') el.disabled = true; // Disable all inputs except submit button
                });
                document.querySelector('button[type="submit"]').disabled = true; // Disable submit button
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

        // Call once when page is loaded
        updateTaskCountdown();
        // Call every second
        setInterval(updateTaskCountdown, 1000);

        // Logic to handle form submission with files (remains the same)
        document.getElementById('taskSubmissionForm').addEventListener('submit', async function(event) {
            event.preventDefault(); // Prevent default form submission

            const form = event.target;
            const formData = new FormData(form);

            // Remove 'answers[X][student_answer]' entries that might be automatically created by FormData
            // for file inputs, because we will add them manually with a different name to avoid conflicts
            // with non-file inputs.
            for (let pair of formData.entries()) {
                if (pair[0].startsWith('answers[') && pair[0].includes('][student_answer]')) {
                    const inputElement = form.querySelector(`[name="${pair[0]}"]`);
                    if (inputElement && inputElement.type === 'file') {
                        formData.delete(pair[0]);
                    }
                }
            }

            // Manually add answers for image inputs
            document.querySelectorAll('input[type="file"][name^="answers"][name$="[student_answer_file]"]').forEach(fileInput => {
                const questionIndexMatch = fileInput.name.match(/answers\[(\d+)\]/);
                if (questionIndexMatch) {
                    const questionIndex = questionIndexMatch[1];

                    if (fileInput.files.length > 0) {
                        formData.append(`answers[${questionIndex}][student_answer]`, fileInput.files[0]);
                    } else {
                        const existingPathInput = form.querySelector(`input[type="hidden"][name="answers[${questionIndex}][student_answer_existing_path]"]`);
                        if (existingPathInput && existingPathInput.value) {
                            formData.append(`answers[${questionIndex}][student_answer]`, existingPathInput.value);
                        }
                    }
                }
            });


            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData, // FormData will automatically set Content-Type: multipart/form-data
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                // Check if response is JSON before parsing
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    const result = await response.json();

                    if (result.success) {
                        // Use custom modal instead of alert
                        showCustomAlert(result.message, 'success');
                        setTimeout(() => {
                            window.location.reload(); // Reload page to display latest status
                        }, 1500); // Give user time to read message
                    } else {
                        let errorMessage = 'Failed to submit task: ' + (result.message || 'An error occurred.');
                        if (result.errors) {
                            let errorMessages = '';
                            for (const key in result.errors) {
                                errorMessages += result.errors[key].join('\n') + '\n';
                            }
                            errorMessage += '\nValidation error:\n' + errorMessages;
                        }
                        showCustomAlert(errorMessage, 'error');
                    }
                } else {
                    // If not JSON, there might be an HTML error from the server
                    const errorText = await response.text();
                    console.error('Server responded with non-JSON:', errorText);
                    showCustomAlert('Server error. Please check console for more details.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showCustomAlert('An error occurred while submitting the task.', 'error');
            }
        });

        // Function to display custom alert (replaces native alert())
        function showCustomAlert(message, type) {
            let alertDiv = document.getElementById('custom-alert');
            if (!alertDiv) {
                alertDiv = document.createElement('div');
                alertDiv.id = 'custom-alert';
                alertDiv.classList.add('fixed', 'top-1/2', 'left-1/2', '-translate-x-1/2', '-translate-y-1/2', 'p-6', 'rounded-lg', 'shadow-xl', 'z-50', 'text-center', 'max-w-sm', 'w-11/12', 'transform', 'scale-0', 'transition-all', 'duration-300', 'ease-out');
                document.body.appendChild(alertDiv);
            }

            alertDiv.innerHTML = `<p class="font-bold text-lg mb-3">${type === 'success' ? 'Sukses!' : 'Error!'}</p><p>${message}</p>`;

            if (type === 'success') {
                alertDiv.classList.remove('bg-red-500', 'text-white');
                alertDiv.classList.add('bg-green-500', 'text-white');
            } else {
                alertDiv.classList.remove('bg-green-500', 'text-white');
                alertDiv.classList.add('bg-red-500', 'text-white');
            }

            // Show alert with animation
            alertDiv.classList.remove('scale-0');
            alertDiv.classList.add('scale-100');

            setTimeout(() => {
                alertDiv.classList.remove('scale-100');
                alertDiv.classList.add('scale-0');
            }, 3000); // Hide after 3 seconds
        }
    </script>
@endsection
