<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogGaji extends Model
{
    use HasFactory;

    protected $fillable = ['id_karyawan', 'gaji', 'tahun', 'bulan', 'tunjangan_jabatan'];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan');
    }
}
