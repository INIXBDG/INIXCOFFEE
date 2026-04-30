<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class no_akun extends Model
{
    use HasFactory;

    protected $fillable = [
        'no',
        'nama_akun',
       
    ];
}
