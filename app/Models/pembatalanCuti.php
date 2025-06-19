<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pembatalanCuti extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_karyawan',
        'id_cuti',
        'bukti_gambar',
        'kronologi',
        'tipe',
        'tanggal_awal',
        'tanggal_akhir',
        'durasi',
        'kontak',
        'alasan',
        'surat_sakit',
        'alasan_approval',
        'approval',
    ];
    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan', 'id');
    }

    public function pengajuancuti()
    {
        return $this->belongsTo(pengajuancuti::class, 'id_cuti', 'id');
    }
}
