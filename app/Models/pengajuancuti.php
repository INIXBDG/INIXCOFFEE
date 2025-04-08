<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pengajuancuti extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_karyawan',
        'tanggal_awal',
        'tanggal_akhir',
        'durasi',
        'kontak',
        'alasan',
        'approval_manager',
        'alasan_manager',
        'tipe',
        'surat_sakit',
        'backup_karyawan1',
        'backup_karyawan2',
    ];
    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan', 'id');
    }
    public function karyawanpengganti1()
    {
        return $this->belongsTo(karyawan::class, 'backup_karyawan1', 'kode_karyawan');
    }
    public function karyawanpengganti2()
    {
        return $this->belongsTo(karyawan::class, 'backup_karyawan2', 'kode_karyawan');
    }
}