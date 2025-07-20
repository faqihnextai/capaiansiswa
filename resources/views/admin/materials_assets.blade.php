<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola Materi & Aset - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
    </style>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-white text-2xl font-bold">Kelola Materi & Aset</h1>
            <nav>
                <a href="{{ route('admin.dashboard') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                    Kembali ke Dashboard
                </a>
            </nav>
        </div>
    </header>

    <main class="container mx-auto p-6 mt-8 bg-white rounded-xl shadow-lg">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Manajemen Materi & Aset Pembelajaran</h2>
        <p class="text-gray-700 mb-4">Di halaman ini, Anda dapat mengelola materi dan aset pembelajaran seperti dokumen, video, atau file lainnya.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-3">Unggah Aset Baru</h3>
                <p class="text-gray-600 mb-4">Formulir untuk mengunggah file materi atau aset baru.</p>
                <form action="{{ route('admin.materials.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Judul Aset</label>
                        <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-2" required>
                    </div>

                    <div>
                        <label for="class_grade" class="block text-sm font-medium text-gray-700">Untuk Kelas</label>
                        <select name="class_grade" id="class_grade" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-2" required>
                            <option value="">Pilih Kelas</option>
                            <option value="4">Kelas 4</option>
                            <option value="5">Kelas 5</option>
                            <option value="6">Kelas 6</option>
                        </select>
                    </div>

                    <div>
                        <label for="asset_type" class="block text-sm font-medium text-gray-700">Jenis Aset</label>
                        <select name="asset_type" id="asset_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-2" required>
                            <option value="">Pilih Jenis</option>
                            <option value="link">Link</option>
                            <option value="file">File (PPT/PDF)</option>
                            <option value="text">Teks Chat</option>
                        </select>
                    </div>

                    <div id="file_upload_section" class="hidden">
                        <label for="file_asset" class="block text-sm font-medium text-gray-700">Unggah File (PPT/PDF)</label>
                        <input type="file" name="file_asset" id="file_asset" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div id="link_input_section" class="hidden">
                        <label for="link_asset" class="block text-sm font-medium text-gray-700">URL Link</label>
                        <input type="url" name="link_asset" id="link_asset" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-2">
                    </div>

                    <div id="text_input_section" class="hidden">
                        <label for="text_asset" class="block text-sm font-medium text-gray-700">Isi Teks Chat</label>
                        <textarea name="text_asset" id="text_asset" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-2"></textarea>
                    </div>

                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                        Unggah Aset
                    </button>
                </form>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-3">Daftar Aset Tersedia</h3>
                <p class="text-gray-600 mb-4">Daftar materi dan aset yang sudah diunggah.</p>
                <!-- Daftar aset akan ditampilkan di sini nanti -->
                <div id="asset_list" class="space-y-3">
                    @if(isset($materials) && $materials->count() > 0)
                        @foreach($materials as $material)
                            <div class="border border-gray-200 p-3 rounded-md shadow-sm">
                                <p class="font-semibold text-gray-800">{{ $material->title }}</p>
                                <p class="text-sm text-gray-600">Kelas: {{ $material->class_grade }}</p>
                                <p class="text-sm text-gray-600">Jenis: {{ ucfirst($material->asset_type) }}</p>
                                @if($material->asset_type == 'link')
                                    <a href="{{ $material->content }}" target="_blank" class="text-blue-500 hover:underline text-sm break-all">{{ $material->content }}</a>
                                @elseif($material->asset_type == 'file')
                                    <a href="{{ asset('storage/' . $material->content) }}" target="_blank" class="text-blue-500 hover:underline text-sm">{{ basename($material->content) }}</a>
                                @else
                                    <p class="text-sm text-gray-700 mt-1">{{ Str::limit($material->content, 100) }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">Diunggah: {{ $material->created_at->format('d M Y H:i') }}</p>
                                <div class="mt-2 flex space-x-2">
                                    <form action="{{ route('admin.materials.destroy', $material->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-500">Belum ada aset yang diunggah.</p>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const assetTypeSelect = document.getElementById('asset_type');
            const fileUploadSection = document.getElementById('file_upload_section');
            const linkInputSection = document.getElementById('link_input_section');
            const textInputSection = document.getElementById('text_input_section');
            const fileAssetInput = document.getElementById('file_asset');
            const linkAssetInput = document.getElementById('link_asset');
            const textAssetInput = document.getElementById('text_asset');

            function toggleAssetInputs() {
                // Sembunyikan semua dan hapus atribut 'required'
                fileUploadSection.classList.add('hidden');
                linkInputSection.classList.add('hidden');
                textInputSection.classList.add('hidden');
                fileAssetInput.removeAttribute('required');
                linkAssetInput.removeAttribute('required');
                textAssetInput.removeAttribute('required');

                const selectedType = assetTypeSelect.value;

                if (selectedType === 'file') {
                    fileUploadSection.classList.remove('hidden');
                    fileAssetInput.setAttribute('required', 'required');
                } else if (selectedType === 'link') {
                    linkInputSection.classList.remove('hidden');
                    linkAssetInput.setAttribute('required', 'required');
                } else if (selectedType === 'text') {
                    textInputSection.classList.remove('hidden');
                    textAssetInput.setAttribute('required', 'required');
                }
            }

            assetTypeSelect.addEventListener('change', toggleAssetInputs);

            // Panggil saat halaman dimuat untuk mengatur tampilan awal
            toggleAssetInputs();
        });
    </script>
</body>
</html>
