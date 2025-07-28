<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Buat Story Baru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container-wrapper {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        /* Style untuk modal kustom */
        .custom-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0s linear 0.3s;
        }
        .custom-modal.show {
            opacity: 1;
            visibility: visible;
            transition: opacity 0.3s ease-in-out, visibility 0s linear 0s;
        }
        .custom-modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 500px;
            text-align: center;
            position: relative;
            transform: translateY(-20px);
            transition: transform 0.3s ease-out;
        }
        .custom-modal.show .custom-modal-content {
            transform: translateY(0);
        }
        .close-button {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
        .camera-stream {
            width: 100%;
            max-height: 400px;
            border-radius: 8px;
            background-color: #000;
            object-fit: contain;
            display: block;
            margin-bottom: 20px;
        }
        .hidden {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-white text-2xl font-bold">Buat Story Baru</h1>
            <a href="{{ route('admin.dashboard') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                Kembali
            </a>
        </div>
    </header>

    <div class="container-wrapper">
        <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-2xl">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Unggah Story</h2>

            <form id="storyForm" action="{{ route('admin.stories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div id="cameraSection">
                    <video id="cameraFeed" class="camera-stream" autoplay playsinline></video>
                    <canvas id="cameraCanvas" class="hidden"></canvas>
                    <div class="flex justify-center space-x-4 mb-6">
                        <button type="button" id="startCameraButton" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 ease-in-out">
                            Buka Kamera
                        </button>
                        <button type="button" id="takePhotoButton" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 ease-in-out hidden">
                            Ambil Foto
                        </button>
                    </div>
                </div>

                <div id="imagePreviewSection" class="hidden">
                    <div id="imagePreviews" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        {{-- Image previews and caption inputs will be added here by JavaScript --}}
                    </div>
                    <div class="flex justify-center">
                        <button type="button" id="addMorePhotosButton" class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 ease-in-out">
                            Tambah Foto Lain
                        </button>
                    </div>
                    <div class="mt-8 text-center">
                        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg text-xl transition duration-300 ease-in-out transform hover:scale-105">
                            Unggah Semua Story
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Custom Modal for Messages --}}
    <div id="customModal" class="custom-modal">
        <div class="custom-modal-content">
            <span class="close-button" id="closeModalButton">&times;</span>
            <p id="modalMessage" class="text-lg font-semibold text-gray-800"></p>
            <button id="modalOkButton" class="mt-6 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-5 rounded-lg">OK</button>
        </div>
    </div>


    <script>
        const video = document.getElementById('cameraFeed');
        const canvas = document.getElementById('cameraCanvas');
        const startCameraButton = document.getElementById('startCameraButton');
        const takePhotoButton = document.getElementById('takePhotoButton');
        const cameraSection = document.getElementById('cameraSection');
        const imagePreviewSection = document.getElementById('imagePreviewSection');
        const imagePreviewsContainer = document.getElementById('imagePreviews');
        const addMorePhotosButton = document.getElementById('addMorePhotosButton');
        const storyForm = document.getElementById('storyForm');

        // Custom Modal elements
        const customModal = document.getElementById('customModal');
        const modalMessage = document.getElementById('modalMessage');
        const closeModalButton = document.getElementById('closeModalButton');
        const modalOkButton = document.getElementById('modalOkButton');

        let stream; // Untuk menyimpan stream kamera
        let photoCount = 0; // Untuk melacak jumlah foto yang diambil

        // Function to show custom modal
        function showCustomModal(message) {
            modalMessage.textContent = message;
            customModal.classList.add('show');
        }

        // Function to hide custom modal
        function hideCustomModal() {
            customModal.classList.remove('show');
        }

        closeModalButton.addEventListener('click', hideCustomModal);
        modalOkButton.addEventListener('click', hideCustomModal);
        customModal.addEventListener('click', function(event) {
            if (event.target === customModal) {
                hideCustomModal();
            }
        });


        startCameraButton.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }); // Coba kamera belakang
                video.srcObject = stream;
                video.play();
                startCameraButton.classList.add('hidden');
                takePhotoButton.classList.remove('hidden');
            } catch (err) {
                console.error("Error accessing camera: ", err);
                showCustomModal('Gagal mengakses kamera. Pastikan Anda memberikan izin akses kamera.');
            }
        });

        takePhotoButton.addEventListener('click', () => {
            if (stream) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Stop the camera stream after taking a photo
                stream.getTracks().forEach(track => track.stop());
                video.srcObject = null; // Clear video feed
                cameraSection.classList.add('hidden'); // Sembunyikan section kamera
                imagePreviewSection.classList.remove('hidden'); // Tampilkan section preview

                const imageDataURL = canvas.toDataURL('image/png');
                addPhotoToPreview(imageDataURL);

                // Setelah foto pertama diambil, ubah tombol menjadi "Tambah Foto Lain"
                takePhotoButton.classList.add('hidden'); // Sembunyikan tombol "Ambil Foto"
                startCameraButton.textContent = 'Ambil Foto Lagi'; // Ubah teks tombol kamera
                startCameraButton.classList.remove('hidden'); // Tampilkan kembali tombol kamera
            } else {
                showCustomModal('Kamera belum aktif. Silakan buka kamera terlebih dahulu.');
            }
        });

        addMorePhotosButton.addEventListener('click', () => {
            cameraSection.classList.remove('hidden'); // Tampilkan kembali section kamera
            imagePreviewSection.classList.add('hidden'); // Sembunyikan section preview
            startCameraButton.classList.remove('hidden'); // Pastikan tombol "Buka Kamera" terlihat
            takePhotoButton.classList.add('hidden'); // Sembunyikan tombol "Ambil Foto"

            // Mulai ulang kamera jika belum aktif
            if (!video.srcObject) {
                startCameraButton.click();
            }
        });


        function addPhotoToPreview(imageDataURL) {
            const photoIndex = photoCount++;

            const previewDiv = document.createElement('div');
            previewDiv.className = 'bg-gray-50 p-4 rounded-lg shadow-md relative';
            previewDiv.innerHTML = `
                <img src="${imageDataURL}" alt="Story Preview ${photoIndex + 1}" class="w-full h-48 object-cover rounded-md mb-3 border border-gray-300">
                <input type="hidden" name="story_images[${photoIndex}]" value="${imageDataURL}">
                <textarea name="captions[${photoIndex}]" rows="2" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Tambahkan caption (opsional)"></textarea>
                <button type="button" class="remove-photo-button absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-lg font-bold">&times;</button>
            `;
            imagePreviewsContainer.appendChild(previewDiv);

            // Add event listener to remove button
            previewDiv.querySelector('.remove-photo-button').addEventListener('click', function() {
                previewDiv.remove();
                if (imagePreviewsContainer.children.length === 0) {
                    imagePreviewSection.classList.add('hidden');
                    cameraSection.classList.remove('hidden');
                    startCameraButton.classList.remove('hidden');
                    takePhotoButton.classList.add('hidden');
                    startCameraButton.textContent = 'Buka Kamera'; // Reset text
                    photoCount = 0; // Reset count if all photos are removed
                    if (stream) {
                         stream.getTracks().forEach(track => track.stop());
                         video.srcObject = null;
                    }
                }
            });
        }


        storyForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            // Ubah dataURL menjadi File objek
            const formData = new FormData();
            const imageInputs = document.querySelectorAll('input[name^="story_images"]');
            const captionInputs = document.querySelectorAll('textarea[name^="captions"]');

            if (imageInputs.length === 0) {
                showCustomModal('Anda belum mengambil foto untuk story.');
                return;
            }

            for (let i = 0; i < imageInputs.length; i++) {
                const imageDataURL = imageInputs[i].value;
                const caption = captionInputs[i].value;

                // Konversi dataURL ke Blob
                const blob = await (await fetch(imageDataURL)).blob();
                formData.append(`story_images[${i}]`, blob, `story_${i}.png`);
                formData.append(`captions[${i}]`, caption);
            }

            // Tambahkan token CSRF
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            // Kirim data menggunakan Fetch API
            try {
                const response = await fetch(storyForm.action, {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json(); // Asumsi server mengembalikan JSON

                if (response.ok) {
                    showCustomModal(result.message || 'Story berhasil diunggah!');
                    // Opsional: Redirect atau reset form
                    setTimeout(() => {
                        window.location.href = "{{ route('admin.dashboard') }}";
                    }, 1500);
                } else {
                    showCustomModal(result.message || 'Gagal mengunggah story. Silakan coba lagi.');
                    console.error('Error response:', result);
                }
            } catch (error) {
                console.error('Error during fetch:', error);
                showCustomModal('Terjadi kesalahan saat mengunggah story.');
            }
        });

    </script>
</body>
</html>
