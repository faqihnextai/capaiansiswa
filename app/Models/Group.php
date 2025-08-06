<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'class_grade'];

    /**
     * Get the students for the group.
     */
    public function students()
    {
        // Relasi satu-ke-banyak: Satu kelompok memiliki banyak siswa
        // Ini adalah perbaikan dari belongsToMany menjadi hasMany
        return $this->hasMany(Student::class);
    }

    /**
     * The tasks that belong to the group.
     */
    public function tasks()
    {
        // Relasi banyak-ke-banyak: Satu kelompok bisa memiliki banyak tugas
        // Menggunakan tabel pivot 'group_task'
        return $this->belongsToMany(Task::class, 'group_task', 'group_id', 'task_id');
    }
}
