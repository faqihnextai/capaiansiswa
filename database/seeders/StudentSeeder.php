<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Group; // Pastikan model Group diimpor

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada grup yang sudah ada di database atau jalankan GroupSeeder terlebih dahulu
        $groupA = Group::where('name', 'Kelompok A')->first();
        $groupB = Group::where('name', 'Kelompok B')->first();
        $groupC = Group::where('name', 'Kelompok C')->first();
        $groupD = Group::where('name', 'Kelompok D')->first(); // Tambahkan ini jika ada Kelompok D
        $groupE = Group::where('name', 'Kelompok E')->first(); // Tambahkan ini jika ada Kelompok E

        if ($groupA) {
            Student::create(['group_id' => $groupA->id, 'name' => 'Siswa 1 Kelas 4A', 'stars' => 5]);
            Student::create(['group_id' => $groupA->id, 'name' => 'Siswa 2 Kelas 4A', 'stars' => 4]);
        }
        if ($groupB) {
            Student::create(['group_id' => $groupB->id, 'name' => 'Siswa 3 Kelas 4B', 'stars' => 3]);
        }
        if ($groupC) {
            Student::create(['group_id' => $groupC->id, 'name' => 'Siswa 4 Kelas 5C', 'stars' => 5]);
            Student::create(['group_id' => $groupC->id, 'name' => 'Siswa 5 Kelas 5C', 'stars' => 4]);
        }
        if ($groupD) { // Tambahkan seeding untuk Kelompok D jika ada
            Student::create(['group_id' => $groupD->id, 'name' => 'Siswa 6 Kelas 5D', 'stars' => 3]);
        }
        if ($groupE) { // Tambahkan seeding untuk Kelompok E jika ada
            Student::create(['group_id' => $groupE->id, 'name' => 'Siswa 7 Kelas 6E', 'stars' => 5]);
        }
    }
}
