<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade'); // Foreign key ke tabel students
            $table->foreignId('achievement_id')->constrained()->onDelete('cascade'); // Foreign key ke tabel achievements
            $table->boolean('is_completed')->default(false); // Status ceklis (true jika sudah selesai)
            $table->timestamps();

            // Menambahkan unique constraint agar satu siswa hanya punya satu entri per capaian
            $table->unique(['student_id', 'achievement_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_achievements');
    }
};
