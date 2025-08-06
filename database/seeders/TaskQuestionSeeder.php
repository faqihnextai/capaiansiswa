<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Question;
use App\Models\Group; // Import model Group
use App\Models\Student; // Import model Student
use Carbon\Carbon;

class TaskQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Buat tugas baru
        $task = Task::create([
            'title' => 'Kuis Sistem Komputer: Hardware, Brainware, Software',
            'class_grade' => 6, // Tugas untuk Kelas 6
            'deadline' => Carbon::create(2025, 8, 13, 23, 59, 59), // 13 Agustus 2025, 23:59:59
        ]);

        // Cari kelompok 'Mordo' di Kelas 6
        $mordoGroup = Group::where('name', 'Mordo')
                           ->where('class_grade', 6)
                           ->first();

        // Inisialisasi array untuk menyimpan ID siswa yang akan dikaitkan dengan tugas
        $studentsToAttach = [];

        if ($mordoGroup) {
            // Kaitkan tugas dengan kelompok 'Mordo'
            $task->groups()->attach($mordoGroup->id);
            $this->command->info('Tugas dikaitkan dengan kelompok: ' . $mordoGroup->name);

            // Tambahkan semua siswa dari kelompok 'Mordo' ke daftar siswa yang akan dikaitkan
            $studentsInMordoGroup = $mordoGroup->students()->pluck('id')->toArray();
            $studentsToAttach = array_merge($studentsToAttach, $studentsInMordoGroup);
            $this->command->info('Menambahkan ' . count($studentsInMordoGroup) . ' siswa dari kelompok ' . $mordoGroup->name);
        } else {
            $this->command->warn('Kelompok "Mordo" untuk Kelas 6 tidak ditemukan.');
        }

        // Tambahkan siswa spesifik yang disebutkan di gambar
        $specificStudentNames = ['julaili', 'muhammad badrur', 'sofian'];
        foreach ($specificStudentNames as $studentName) {
            // Cari siswa berdasarkan nama dan kelas 6
            $student = Student::where('name', $studentName)
                              ->whereHas('group', function ($query) {
                                  $query->where('class_grade', 6);
                              })
                              ->first();

            if ($student) {
                // Tambahkan ID siswa ke daftar jika belum ada
                if (!in_array($student->id, $studentsToAttach)) {
                    $studentsToAttach[] = $student->id;
                    $this->command->info('Menambahkan siswa spesifik: ' . $student->name);
                }
            } else {
                $this->command->warn('Siswa "' . $studentName . '" di Kelas 6 tidak ditemukan.');
            }
        }

        // Lakukan attach hanya jika ada siswa yang akan dikaitkan
        if (!empty($studentsToAttach)) {
            // Gunakan array_unique untuk menghindari duplikasi jika siswa sudah ditambahkan dari kelompok
            $task->students()->attach(array_unique($studentsToAttach));
            $this->command->info('Tugas berhasil dikaitkan dengan total ' . count(array_unique($studentsToAttach)) . ' siswa.');
        } else {
            $this->command->error('Tidak ada siswa yang dikaitkan dengan tugas ini.');
        }


        $questionsData = [
            // Soal Hardware
            [
                'content' => 'Perangkat keras komputer yang berfungsi sebagai otak utama pemrosesan adalah...',
                'options' => ['a' => 'RAM', 'b' => 'CPU', 'c' => 'Hard Drive', 'd' => 'Monitor'],
                'correct_answer' => 'b',
            ],
            [
                'content' => 'Komponen yang digunakan untuk menyimpan data secara permanen adalah...',
                'options' => ['a' => 'RAM', 'b' => 'Processor', 'c' => 'Hard Disk Drive', 'd' => 'Keyboard'],
                'correct_answer' => 'c',
            ],
            [
                'content' => 'Perangkat output yang menampilkan visual dari komputer adalah...',
                'options' => ['a' => 'Printer', 'b' => 'Scanner', 'c' => 'Speaker', 'd' => 'Monitor'],
                'correct_answer' => 'd',
            ],
            [
                'content' => 'Alat input yang digunakan untuk mengetikkan teks dan perintah adalah...',
                'options' => ['a' => 'Mouse', 'b' => 'Microphone', 'c' => 'Keyboard', 'd' => 'Webcam'],
                'correct_answer' => 'c',
            ],
            [
                'content' => 'RAM (Random Access Memory) berfungsi sebagai...',
                'options' => ['a' => 'Penyimpanan permanen', 'b' => 'Penyimpanan sementara', 'c' => 'Unit pemrosesan grafis', 'd' => 'Pengatur daya'],
                'correct_answer' => 'b',
            ],
            [
                'content' => 'Yang bukan termasuk perangkat keras output adalah...',
                'options' => ['a' => 'Speaker', 'b' => 'Printer', 'c' => 'Microphone', 'd' => 'Proyektor'],
                'correct_answer' => 'c',
            ],
            [
                'content' => 'Bagian dari CPU yang bertugas melakukan perhitungan aritmatika dan logika adalah...',
                'options' => ['a' => 'Control Unit (CU)', 'b' => 'Arithmetic Logic Unit (ALU)', 'c' => 'Register', 'd' => 'Cache Memory'],
                'correct_answer' => 'b',
            ],
            [
                'content' => 'Port yang umum digunakan untuk menghubungkan printer, keyboard, atau mouse adalah...',
                'options' => ['a' => 'HDMI', 'b' => 'VGA', 'c' => 'USB', 'd' => 'Ethernet'],
                'correct_answer' => 'c',
            ],
            [
                'content' => 'Motherboard berfungsi sebagai...',
                'options' => ['a' => 'Penyimpan data', 'b' => 'Papan sirkuit utama', 'c' => 'Pendingin CPU', 'd' => 'Catu daya'],
                'correct_answer' => 'b',
            ],
            [
                'content' => 'Perangkat keras yang mengubah arus listrik AC menjadi DC untuk komponen komputer adalah...',
                'options' => ['a' => 'Stabilizer', 'b' => 'UPS', 'c' => 'Power Supply Unit (PSU)', 'd' => 'Regulator'],
                'correct_answer' => 'c',
            ],

            // Soal Software
            [
                'content' => 'Sistem operasi yang paling banyak digunakan di komputer pribadi adalah...',
                'options' => ['a' => 'Linux', 'b' => 'macOS', 'c' => 'Windows', 'd' => 'Android'],
                'correct_answer' => 'c',
            ],
            [
                'content' => 'Contoh software aplikasi untuk pengolah kata adalah...',
                'options' => ['a' => 'Microsoft Excel', 'b' => 'Adobe Photoshop', 'c' => 'Microsoft Word', 'd' => 'Google Chrome'],
                'correct_answer' => 'c',
            ],
            [
                'content' => 'Bahasa pemrograman yang sering digunakan untuk pengembangan web frontend adalah...',
                'options' => ['a' => 'Python', 'b' => 'Java', 'c' => 'JavaScript', 'd' => 'C++'],
                'correct_answer' => 'c',
            ],
            [
                'content' => 'Software yang berfungsi untuk melindungi komputer dari virus adalah...',
                'options' => ['a' => 'Browser', 'b' => 'Antivirus', 'c' => 'Spreadsheet', 'd' => 'Media Player'],
                'correct_answer' => 'b',
            ],
            [
                'content' => 'Yang bukan termasuk sistem operasi adalah...',
                'options' => ['a' => 'Ubuntu', 'b' => 'iOS', 'c' => 'Microsoft Office', 'd' => 'Android'],
                'correct_answer' => 'c',
            ],
            [
                'content' => 'Software yang digunakan untuk membuat presentasi adalah...',
                'options' => ['a' => 'Microsoft Word', 'b' => 'Microsoft Excel', 'c' => 'Microsoft PowerPoint', 'd' => 'Adobe Acrobat'],
                'correct_answer' => 'c',
            ],
            [
                'content' => 'Compiler adalah jenis software yang berfungsi untuk...',
                'options' => ['a' => 'Menjalankan program', 'b' => 'Menerjemahkan kode sumber ke kode mesin', 'c' => 'Mengelola database', 'd' => 'Mengedit gambar'],
                'correct_answer' => 'b',
            ],

            // Soal Brainware
            [
                'content' => 'Istilah untuk pengguna komputer yang mengoperasikan sistem adalah...',
                'options' => ['a' => 'Hardware', 'b' => 'Software', 'c' => 'Brainware', 'd' => 'Firmware'],
                'correct_answer' => 'c',
            ],
            [
                'content' => 'Seorang yang ahli dalam merancang dan membangun program komputer disebut...',
                'options' => ['a' => 'Operator', 'b' => 'Analis Sistem', 'c' => 'Programmer', 'd' => 'Teknisi Jaringan'],
                'correct_answer' => 'c',
            ],
            [
                'content' => 'Orang yang bertanggung jawab atas pengelolaan dan pemeliharaan database adalah...',
                'options' => ['a' => 'Network Administrator', 'b' => 'Database Administrator', 'c' => 'Web Developer', 'd' => 'Data Entry'],
                'correct_answer' => 'b',
            ],
        ];

        $scorePerQuestion = 5; // Setiap soal bernilai 5 poin

        foreach ($questionsData as $data) {
            Question::create([
                'task_id' => $task->id,
                'type' => 'multiple_choice',
                'content' => $data['content'],
                'options' => json_encode($data['options']),
                'correct_answer' => json_encode($data['correct_answer']),
                'score' => $scorePerQuestion,
                'media_path' => null, // Tidak ada gambar
            ]);
        }

        $this->command->info('Tugas dan 20 soal Pilihan Ganda tentang Sistem Komputer berhasil ditambahkan!');
    }
}
