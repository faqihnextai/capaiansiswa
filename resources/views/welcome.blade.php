@extends('layouts.app') {{-- Menggunakan layout utama yang sudah dibuat --}}

@section('content')
    <h1 class="text-4xl font-extrabold mb-8 text-center text-gray-800"></h1>

    {{-- Bagian filter kelas dihilangkan sesuai permintaan --}}
    {{-- Kode dropdown kelas sebelumnya yang dihapus:
    <div id="classDropdownSection" class="mb-10 p-6 bg-white rounded-lg shadow-md max-w-lg mx-auto">
        <label for="classSelect" class="block text-xl font-semibold text-gray-700 mb-3">Pilih Kelas:</label>
        <select id="classSelect" class="mt-1 block w-full pl-4 pr-12 py-3 text-lg border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out">
            <option value="">Tampilkan Semua Kelas</option>
            <option value="4">Kelas 4</option>
            <option value="5">Kelas 5</option>
            <option value="6">Kelas 6</option>
        </select>
    </div>
    --}}

    <div id="studentCardsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        {{-- Loop melalui data kelompok yang dikirim dari controller (Group::with('students')->get()) --}}
        {{-- @forelse digunakan untuk menampilkan pesan jika tidak ada data --}}
        @forelse($groups as $group)
            {{-- Setiap card mewakili satu kelompok --}}
            <div class="student-card bg-white shadow-md rounded-lg p-6" data-class="{{ $group->class_grade }}" data-group-id="{{ $group->id }}">
                <h3 class="font-bold text-xl text-gray-800 mb-4">Kelompok {{ $group->name }} - Kelas {{ $group->class_grade }}</h3>
                <p class="text-gray-600 mb-3">Anggota Kelompok:</p>
                @if($group->students->isNotEmpty())
                    <ul class="list-none p-0">
                        {{-- Loop melalui setiap siswa dalam kelompok ini --}}
                        @foreach($group->students as $student)
                            <li class="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                                <span class="text-gray-700">{{ $student->name }}</span>
                                <span class="stars text-yellow-500 text-lg">
                                    {{-- Menampilkan emoji bintang berdasarkan nilai 'stars' --}}
                                    {{ str_repeat('⭐️', $student->stars ?? 0) }}
                                    {{-- Menampilkan pesan jika nilai bintang belum diisi --}}
                                    @if(is_null($student->stars))
                                        <span class="text-gray-400 text-sm ml-1">(Belum Dinilai)</span>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    {{-- Pesan jika kelompok tidak memiliki anggota --}}
                    <p class="text-gray-500 italic">Belum ada anggota di kelompok ini.</p>
                @endif
            </div>
        @empty
            {{-- Pesan jika tidak ada data kelompok sama sekali dari database --}}
            <p class="col-span-full text-center text-gray-600 text-xl p-8">Belum ada data kelompok yang diinput oleh admin.</p>
        @endforelse
    </div>

    <script>
        // Fungsi ini dipanggil dari app.blade.php setelah role siswa didapatkan dari alert
        window.updateContentBasedOnRole = function(role) {
            console.log("Welcome.blade: Memperbarui konten untuk kelas:", role);
            filterCardsByClass(role); // Panggil fungsi filter kartu
        };

        // Fungsi untuk menyaring kartu kelompok berdasarkan kelas yang dipilih (dari alert role)
        function filterCardsByClass(selectedClass) {
            const cards = document.querySelectorAll('.student-card'); // Ambil semua kartu kelompok
            cards.forEach(card => {
                const cardClass = card.getAttribute('data-class'); // Dapatkan nilai data-class dari kartu

                // Logika filter:
                // Jika selectedClass kosong (artinya "Tampilkan Semua Kelas") ATAU
                // Jika kelas kartu cocok dengan kelas yang dipilih dari alert
                if (selectedClass === "" || cardClass == selectedClass) { // Menggunakan '==' untuk perbandingan angka
                    card.style.display = 'block'; // Tampilkan kartu
                } else {
                    card.style.display = 'none'; // Sembunyikan kartu
                }
            });
        }

        // Panggil fungsi filter saat halaman pertama kali dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const storedRole = sessionStorage.getItem('siswa_role'); // Ambil role siswa dari sessionStorage

            if (storedRole) {
                // Jika ada role tersimpan, filter kartu berdasarkan role tersebut
                filterCardsByClass(storedRole);
            } else {
                // Jika tidak ada role tersimpan (misal, pertama kali buka), tampilkan semua kartu
                filterCardsByClass(""); // Panggil dengan string kosong untuk menampilkan semua
            }
        });
    </script>
@endsection
