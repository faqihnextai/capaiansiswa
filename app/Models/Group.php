<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'class_grade',
    ];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // Definisikan relasi many-to-many dengan Task
    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }
}