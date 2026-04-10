<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodoAdministrasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'case',
        'status',
        'solusi',
        'catatan',
        'tanggal_selesai',
        'dokumen',
    ];
}
