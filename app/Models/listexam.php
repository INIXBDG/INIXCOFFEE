<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class listexam extends Model
{
    use HasFactory;
    protected $fillable = [
        'provider',
        'kode_exam',
        'nama_exam',
        'vendor',
    ];
}
