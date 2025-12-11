<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenambahanSouvenir extends Model
{
    use HasFactory;

    protected $table = 'penambahan_souvenirs';

    protected $fillable = [
        'id_rkm',
        'id_karyawan',
        'id_souvenir',
        'nama',
        'jabatan',
        'qty',
        'tanggal',
    ];

    /**
     * Relasi ke Souvenir
     */
    public function souvenir()
    {
        return $this->belongsTo(souvenir::class, 'id_souvenir', 'id');
    }

    /**
     * Relasi ke RKM (Asumsi Anda memiliki model Rkm)
     */
    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }
}
