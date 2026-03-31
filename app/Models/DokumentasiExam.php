<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumentasiExam extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_registrasi',
        'nama_exam',
        'tanggal_perusahaan',
        'skor',
        'dokumentasi',
        'invoice',
        'keterangan_lulus'
    ];

    public function registrasi()
    {
        return $this->belongsTo(Registrasi::class, 'id_registrasi', 'id');
    }
}
