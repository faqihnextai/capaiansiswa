<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id', // Menambahkan task_id ke fillable
        'type',
        'content',
        'media_path',
        'options',
        'correct_answer',
        'score', // Menambahkan score untuk nilai soal
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
    ];

    /**
     * Get the task that owns the question.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
