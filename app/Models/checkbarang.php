<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class checkbarang extends Model
{
    use HasFactory;
    protected $fillable = [
        'idbarang', 
        'tanggal_pemeriksaan',
        'interval',
        'kondisi', 
        'catatan',
        'inspector'
    ];
}
