<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class lembur extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_karyawan',
        'tanggal_spl',
        'uraian_tugas',
        'waktu_lembur',
        'tanggal_lembur',
        'jam_mulai',
        'jam_selesai',
        'foto_masuk',
        'foto_selesai',
        'approval_karyawan',
        'keterangan',
        'id_hitung_lembur',
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan', 'id');
    }

    public function hitunglembur()
    {
        return $this->belongsTo(hitunglembur::class, 'id_hitung_lembur', 'id');
    }
}
