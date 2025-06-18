<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class service extends Model
{
    use HasFactory;
    protected $fillable = [
        'idbarang',
        'tanggal_service',
        'deskripsi',
        'harga',
        'user'
    ];

}
