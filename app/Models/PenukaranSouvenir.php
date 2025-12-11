<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenukaranSouvenir extends Model
{
    use HasFactory;

    protected $table = 'penukaran_souvenirs';
    protected $guarded = [];
    protected $fillable = [
        'id_rkm',
        'id_regist',
        'id_souvenir_lama',
        'id_souvenir_baru',
        'tanggal_tukar',
    ];

    // Relasi ke RKM
    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm');
    }

    // Relasi ke Registrasi
    public function regist()
    {
        return $this->belongsTo(Registrasi::class, 'id_regist');
    }

    // Relasi Souvenir Lama (PENTING: Pastikan Model target di-import atau full namespace)
    public function souvenirOld()
    {
        // Menggunakan namespace explisit untuk memastikan class ditemukan
        return $this->belongsTo(\App\Models\souvenir::class, 'id_souvenir_lama');
    }

    // Relasi Souvenir Baru
    public function souvenirNew()
    {
        return $this->belongsTo(\App\Models\souvenir::class, 'id_souvenir_baru');
    }
}
