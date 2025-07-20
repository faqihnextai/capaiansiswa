<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'class_grade',
        'asset_type',
        'content', // Ini akan menyimpan path file, URL link, atau teks chat
    ];
}
