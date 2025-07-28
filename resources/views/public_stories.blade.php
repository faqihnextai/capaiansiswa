@extends('layouts.app')

@section('content')
<div class="relative w-full h-screen bg-black flex items-center justify-center overflow-hidden">
    <div id="story-viewer" class="relative w-full h-full flex items-center justify-center">
        @if($activeStories->isEmpty())
            <p class="text-white text-xl">Tidak ada story aktif saat ini.</p>
        @else
            @foreach($activeStories as $index => $story)
                <div id="story-slide-{{ $index }}" class="story-slide absolute inset-0 w-full h-full flex flex-col justify-center items-center bg-black transition-opacity duration-500 ease-in-out opacity-0 {{ $index === 0 ? 'opacity-100 z-10' : 'z-0' }}" data-index="{{ $index }}">
                    <img src="{{ Storage::url($story->image_path) }}" alt="{{ $story->caption }}" class="story-image max-w-full max-h-full object-contain rounded-lg shadow-lg" style="transform: scale(1); transition: transform 0.1s ease-out;">
                    @if($story->caption)
                        <div class="absolute bottom-1/4 md:bottom-20 bg-black bg-opacity-50 p-3 rounded-lg text-white text-center w-3/4 md:w-1/2">
                            <p class="text-lg">{{ $story->caption }}</p>
                        </div>
                    @endif
                    <div class="absolute top-4 w-full flex justify-center space-x-1 px-4">
                        @foreach($activeStories as $prog_index => $prog_story)
                            <div class="h-1 bg-gray-600 rounded flex-grow" id="progress-bar-{{ $prog_index }}">
                                <div class="h-full bg-white rounded" style="width: 0%;"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

        {{-- Navigation buttons --}}
        @if($activeStories->count() > 1)
            <button id="prevStory" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-20 p-3 rounded-full text-white text-xl focus:outline-none hover:bg-opacity-40 z-20">&#10094;</button>
            <button id="nextStory" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-20 p-3 rounded-full text-white text-xl focus:outline-none hover:bg-opacity-40 z-20">&#10095;</button>
        @endif
        
        {{-- Close button --}}
        <a href="{{ route('public.dashboard') }}" class="absolute top-4 right-4 text-white text-3xl z-20">
            &times;
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stories = @json($activeStories);
        const storyViewer = document.getElementById('story-viewer');
        const prevButton = document.getElementById('prevStory');
        const nextButton = document.getElementById('nextStory');
        let currentStoryIndex = 0;
        let storyInterval;
        const storyDuration = 7000; // Durasi tampil setiap story (7 detik)
        let isPaused = false;
        let startProgressTime = 0; // Waktu mulai progress bar
        let elapsedPausedTime = 0; // Waktu yang sudah terlewat saat dijeda

        // Variabel untuk pinch-to-zoom
        let initialPinchDistance = 0;
        let currentScale = 1;
        let originalScale = 1; // Skala asli saat zoom dimulai
        let activeImage = null; // Gambar yang sedang aktif

        function showStory(index) {
            // Sembunyikan semua story slide
            storyViewer.querySelectorAll('.story-slide').forEach(slide => {
                slide.classList.remove('opacity-100', 'z-10');
                slide.classList.add('opacity-0', 'z-0');
            });
            // Reset semua progress bar
            storyViewer.querySelectorAll('[id^="progress-bar-"] > div').forEach(bar => {
                bar.style.width = '0%';
                bar.style.transition = 'none'; // Hentikan transisi saat reset
            });

            // Tampilkan story saat ini
            const currentSlide = document.getElementById(`story-slide-${index}`);
            if (currentSlide) {
                currentSlide.classList.add('opacity-100', 'z-10');
                activeImage = currentSlide.querySelector('.story-image'); // Ambil gambar aktif
                currentScale = 1; // Reset skala saat ganti story
                activeImage.style.transform = `scale(${currentScale})`;
                startProgressBar(index);
            }
        }

        function startProgressBar(index) {
            const progressBarFill = document.querySelector(`#progress-bar-${index} > div`);
            // Reset transisi sebelum memulai animasi
            if (progressBarFill) {
                progressBarFill.style.width = '0%';
                progressBarFill.style.transition = 'none';
            }

            // Atur ulang elapsed time
            elapsedPausedTime = 0;
            startProgressTime = Date.now();

            clearTimeout(storyInterval); // Clear any existing interval
            animateProgressBar(progressBarFill, 0); // Mulai animasi dari 0
        }

        function animateProgressBar(progressBarFill, currentWidth) {
            if (!progressBarFill || isPaused) return;

            const now = Date.now();
            const timeElapsed = now - startProgressTime + elapsedPausedTime;
            const percentage = (timeElapsed / storyDuration) * 100;

            if (percentage >= 100) {
                progressBarFill.style.width = '100%';
                nextStory();
                return;
            }

            progressBarFill.style.width = `${percentage}%`;
            progressBarFill.style.transition = `width ${(storyDuration - timeElapsed) / 1000}s linear`;
            
            storyInterval = setTimeout(() => {
                animateProgressBar(progressBarFill, percentage);
            }, 100); // Perbarui setiap 100ms untuk kelancaran
        }


        function pauseStory() {
            if (isPaused) return;
            isPaused = true;
            clearTimeout(storyInterval);
            const progressBarFill = document.querySelector(`#progress-bar-${currentStoryIndex} > div`);
            if (progressBarFill) {
                // Simpan posisi lebar progress bar saat dijeda
                const computedWidth = window.getComputedStyle(progressBarFill).width;
                progressBarFill.style.transition = 'none'; // Hentikan animasi
                progressBarFill.style.width = computedWidth; // Tetapkan lebar saat ini
                
                // Hitung berapa waktu yang sudah berlalu
                const currentPercentage = parseFloat(computedWidth) / progressBarFill.parentElement.offsetWidth * 100;
                elapsedPausedTime = (currentPercentage / 100) * storyDuration;
            }
        }

        function resumeStory() {
            if (!isPaused) return;
            isPaused = false;
            startProgressTime = Date.now(); // Reset start time saat melanjutkan
            const progressBarFill = document.querySelector(`#progress-bar-${currentStoryIndex} > div`);
            animateProgressBar(progressBarFill, 0); // Lanjutkan animasi dari posisi terakhir
        }

        function nextStory() {
            currentStoryIndex++;
            if (currentStoryIndex >= stories.length) {
                // Semua story sudah dilihat, kembali ke dashboard
                window.location.href = "{{ route('public.dashboard') }}";
                sessionStorage.setItem('story_seen', 'true'); // Tandai story sudah dilihat
            } else {
                showStory(currentStoryIndex);
            }
        }

        function prevStory() {
            currentStoryIndex--;
            if (currentStoryIndex < 0) {
                currentStoryIndex = 0; // Tetap di story pertama jika sudah paling awal
            }
            showStory(currentStoryIndex);
        }

        if (prevButton) {
            prevButton.addEventListener('click', () => {
                clearTimeout(storyInterval); // Clear interval saat navigasi manual
                isPaused = false; // Pastikan tidak dijeda saat navigasi manual
                prevStory();
            });
        }
        if (nextButton) {
            nextButton.addEventListener('click', () => {
                clearTimeout(storyInterval); // Clear interval saat navigasi manual
                isPaused = false; // Pastikan tidak dijeda saat navigasi manual
                nextStory();
            });
        }

        // --- Pinch-to-Zoom Logic ---
        storyViewer.addEventListener('touchstart', (e) => {
            if (e.touches.length === 2) {
                initialPinchDistance = getPinchDistance(e);
                originalScale = currentScale;
                pauseStory(); // Jeda story saat zoom
            }
        });

        storyViewer.addEventListener('touchmove', (e) => {
            if (e.touches.length === 2 && initialPinchDistance > 0) {
                e.preventDefault(); // Mencegah scrolling
                const currentPinchDistance = getPinchDistance(e);
                const scaleFactor = currentPinchDistance / initialPinchDistance;
                currentScale = originalScale * scaleFactor;
                
                // Batasi skala
                if (currentScale < 1) currentScale = 1;
                if (currentScale > 3) currentScale = 3; // Max zoom 3x
                
                if (activeImage) {
                    activeImage.style.transform = `scale(${currentScale})`;
                }
            }
        }, { passive: false }); // Penting untuk mencegah `e.preventDefault()` error

        storyViewer.addEventListener('touchend', (e) => {
            initialPinchDistance = 0;
            // Jika skala kembali ke 1, lanjutkan story
            if (currentScale === 1) {
                resumeStory();
            }
        });

        function getPinchDistance(e) {
            const touch1 = e.touches[0];
            const touch2 = e.touches[1];
            return Math.sqrt(
                Math.pow(touch2.clientX - touch1.clientX, 2) +
                Math.pow(touch2.clientY - touch1.clientY, 2)
            );
        }

        // --- Pause/Resume on touch hold ---
        storyViewer.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) { // Hanya untuk satu jari
                pauseStory();
            }
        });

        storyViewer.addEventListener('touchend', (e) => {
            // Lanjutkan jika tidak ada pinch-to-zoom yang sedang aktif (initialPinchDistance === 0)
            if (initialPinchDistance === 0) {
                resumeStory();
            }
        });

        // Tampilkan story pertama saat halaman dimuat
        if (stories.length > 0) {
            showStory(currentStoryIndex);
        }
    });
</script>
@endsection
