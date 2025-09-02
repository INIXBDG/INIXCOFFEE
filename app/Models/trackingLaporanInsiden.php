<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class trackingLaporanInsiden extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_laporanInsiden',
        'responder',
        'status',
        'solusi',
        'tanggal_response',
        'waktu_response',
        'keterangan'
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'responder', 'id');
    }

    public function laporanInsiden()
    {
        return $this->belongsTo(laporanInsiden::class, 'id_laporanInsiden', 'id');
    }
}
