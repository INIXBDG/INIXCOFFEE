<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TunjanganKaryawan extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_karyawan',
        'bulan',
        'tahun',
        'jenis_tunjangan',
        'keterangan',
        'jumlah_absensi',
        'total',
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan');
    }

    public function jenistunjangan()
    {
        return $this->belongsTo(jenistunjangan::class, 'jenis_tunjangan');
    }
}
