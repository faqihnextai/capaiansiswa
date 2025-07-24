// database/migrations/YYYY_MM_DD_HHMMSS_create_submissions_table.php
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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->json('answers')->nullable(); // Menyimpan jawaban siswa (JSON)
            $table->integer('score')->nullable(); // Nilai tugas
            $table->text('feedback')->nullable(); // Umpan balik dari admin
            $table->timestamp('submitted_at')->nullable(); // Waktu pengumpulan
            $table->boolean('is_completed')->default(false); // Status selesai/belum
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};