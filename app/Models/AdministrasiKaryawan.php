<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdministrasiKaryawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_administrasi',
        'id_karyawan',
        'status',
        'dateline',
        'tanggal_selesai',
        'keterangan',
        'bukti_transfer'
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan');
    }
}
