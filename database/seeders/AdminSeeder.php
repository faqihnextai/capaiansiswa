<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Jalankan seeder database.
     */
    public function run()
    {
        Admin::create([
            'name' => 'Admin Utama',
            'email' => 'faqihloh@gmail.com', // Ganti dengan email Anda
            'password' => Hash::make('password'), // Ganti dengan password yang kuat
        ]);
    }
}
