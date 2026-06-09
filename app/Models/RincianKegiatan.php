<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RincianKegiatan extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_kegiatan',
        'hal',
        'rincian',
        'qty',
        'harga_satuan',
        'total',
        'id_karyawan',
        'tipe',
        'status',
        'tanggal',
    ];

    public function kegiatan(){
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan', 'id');
    }
}
