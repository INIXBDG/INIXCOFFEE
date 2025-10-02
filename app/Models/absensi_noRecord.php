<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class absensi_noRecord extends Model
{
    use HasFactory;

   protected $fillable = [
    'id_karyawan',
    'jenis_PK',
    'kendala',
    'id_absen',
    'bukti_gambar',
    'kronologi',
    'tanggal',
    'approval',
    'approved_by',
    'alasan_approval',
    'approval_date',
    'waktu_masuk',
    'waktu_pulang',
];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan', 'id');
    }

    public function absensiKaryawan()
    {
        return $this->belongsTo(AbsensiKaryawan::class, 'id_absen', 'id');
    }
}
