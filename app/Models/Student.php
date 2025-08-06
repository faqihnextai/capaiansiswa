<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'group_id',
        'stars' // Tambahkan ini jika belum ada
    ];

    /**
     * Definisi relasi: Satu siswa milik satu kelompok.
     */
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'student_task', 'student_id', 'task_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Definisi relasi: Satu siswa memiliki banyak entri capaian (StudentAchievement).
     */
    public function studentAchievements()
    {
        return $this->hasMany(StudentAchievement::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

}
