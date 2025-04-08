<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiKaryawan extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_karyawan',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'waktu_keterlambatan',
        'keterangan',
        'keterangan_pulang',
        'foto',
    ];
    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan', 'id');
    }
}
