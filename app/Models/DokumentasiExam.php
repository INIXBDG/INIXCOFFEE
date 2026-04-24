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
        'tanggal_pelaksanaan',
        'skor',
        'dokumentasi',
        'invoice',
        'keterangan_lulus'
    ];

    public function registrasi()
    {
        return $this->belongsTo(registexam::class, 'id_registrasi', 'id');
    }
}
