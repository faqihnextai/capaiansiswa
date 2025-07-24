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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->enum('type', ['multiple_choice', 'essay', 'true_false', 'matching', 'image_input']);
            $table->text('content'); // Isi soal
            $table->string('media_path')->nullable(); // Path gambar/video
            $table->json('options')->nullable(); // Untuk PG (pilihan A,B,C,D), Menjodohkan (pasangan), atau instruksi input gambar
            $table->json('correct_answer')->nullable(); // Untuk PG (huruf), Benar/Salah (boolean), Esai (kunci/panduan)
            $table->integer('score')->default(0); // <--- TAMBAHKAN .default(0) DI SINI
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
     {
        Schema::dropIfExists('questions');
    }
};
