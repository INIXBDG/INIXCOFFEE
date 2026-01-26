<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;
    protected $casts = [
        'id_peserta' => 'array',
    ];

    protected $fillable = [
        'nama_kegiatan',
        'waktu_kegiatan',
        'lama_kegiatan',
        'pic',
        'status',
        'menunggu',
        'approved',
        'pencairan',
        'selesai',
        'tipe',
    ];

    public function rincian()
    {
        return $this->hasMany(RincianKegiatan::class, 'id_kegiatan', 'id');
    }
}
