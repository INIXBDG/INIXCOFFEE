<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingCatering extends Model
{
    use HasFactory;

    protected $fillable = ['id_catering', 'id_karyawan', 'tracking', 'tanggal', 'keterangan'];
}
