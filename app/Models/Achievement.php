<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_grade',
        'description',
    ];

    /**
     * Definisi relasi: Satu kriteria capaian bisa dimiliki oleh banyak entri student_achievements.
     */
    public function studentAchievements()
    {
        return $this->hasMany(StudentAchievement::class);
    }
}
