<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    // Kolom yang bisa diisi secara massal
    protected $fillable = [
        'name',
        'class_grade',
    ];

    /**
     * Definisi relasi: Satu kelompok memiliki banyak siswa.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
