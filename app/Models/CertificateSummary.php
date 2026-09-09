<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateSummary extends Model
{
    use HasFactory;
        protected $fillable = [
        'type',
        'no_sertifikat',
        'nama_peserta',
        'perusahaan',
        'materi',
        'awal_training',
        'akhir_training',
        'keterangan'
    ];
}
