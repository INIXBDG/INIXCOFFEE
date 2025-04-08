<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class eksam extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice',
        'tanggal_pengajuan',
        'id_rkm',
        'materi',
        'perusahaan',
        'mata_uang',
        'harga',
        'biaya_admin',
        'harga_rupiah',
        'kurs',
        'kurs_dollar',
        'pax',
        'total',
        'kode_exam',
        'total_pax',
        'keterangan',
        'status',
        'kode_karyawan',
    ];

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'kode_karyawan', 'kode_karyawan');
    }
    public function kodeeksam()
    {
        return $this->belongsTo(listexam::class, 'kode_exam', 'kode_exam');
    }
    public function registexam()
    {
        return $this->hasMany(Registexam::class, 'id_exam', 'id');
    }
    public function approvalexam()
    {
        return $this->belongsTo(approvalexam::class, 'id', 'id_exam');
    }
}
