<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'class_grade',
        'deadline',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_task', 'task_id', 'group_id');
    }

    // Tambahkan relasi ini untuk siswa
    public function students()
    {
        // Asumsi tabel pivot bernama 'student_task'
        // Dan foreign key di tabel pivot adalah 'task_id' dan 'student_id'
        return $this->belongsToMany(Student::class, 'student_task', 'task_id', 'student_id');
    }
}
