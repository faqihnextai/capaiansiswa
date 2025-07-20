<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'achievement_id',
        'is_completed',
    ];

    /**
     * Definisi relasi: Satu entri student_achievement milik satu siswa.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Definisi relasi: Satu entri student_achievement terkait dengan satu kriteria capaian.
     */
    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }
}
