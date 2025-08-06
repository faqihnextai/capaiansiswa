<?php

// Langkah 1: Buat file migration
// Jalankan perintah ini di terminal:
// php artisan make:migration create_admins_table

// Kemudian, ganti isi file migration yang baru dibuat dengan kode di bawah ini.
// File akan berada di: database/migrations/YYYY_MM_DD_HHMMSS_create_admins_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Rollback migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};