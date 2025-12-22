<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaModul extends Model
{
    use HasFactory;
    protected $fillable = [
        'no_modul',
        'modul',
        'nama_peserta',
        'perusahaan_id',
        'email',
        'awal_training',
        'akhir_training',
    ];

    public function nomorModul()
    {
        return $this->belongsTo(NomorModul::class, 'no_modul', 'id');
    }

    public function dataModul()
    {
        return $this->belongsTo(Modul::class, 'modul', 'id');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id', 'id');
    }
}
