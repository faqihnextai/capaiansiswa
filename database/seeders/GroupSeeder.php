<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Group::create(['name' => 'Kelompok A', 'class_grade' => 4]);
        Group::create(['name' => 'Kelompok B', 'class_grade' => 4]);
        Group::create(['name' => 'Kelompok C', 'class_grade' => 5]);
        Group::create(['name' => 'Kelompok D', 'class_grade' => 5]);
        Group::create(['name' => 'Kelompok E', 'class_grade' => 6]);
    }
}
