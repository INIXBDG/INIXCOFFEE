<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tickets extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_karyawan',
        'divisi',
        'kategori',
        'keperluan',
        'detail_kendala',
        'tanggal_response',
        'jam_response',
        'pic',
        'penanganan',
        'status',
        'keterangan',
        'tanggal_selesai',
        'jam_selesai',
        'tingkat_kesulitan',
        'timestamp',
        'row',
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'nama_karyawan', 'nama_lengkap');
    }
}
