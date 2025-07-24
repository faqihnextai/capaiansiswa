<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'student_id',
        'answers',
        'score',
        'feedback',
        'submitted_at',
        'is_completed',
    ];

    protected $casts = [
        'answers' => 'array',
        'submitted_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}