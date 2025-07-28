// database/migrations/YYYY_MM_DD_HHMMSS_remove_group_id_from_tasks_table.php
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
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Jika Anda ingin bisa rollback, Anda perlu menambahkan kembali kolom ini
            // $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('cascade');
        });
    }
};