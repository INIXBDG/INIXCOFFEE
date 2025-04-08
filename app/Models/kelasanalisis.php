<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kelasanalisis extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id_rkm',
        'pax',
        'durasi',
        'total_harga_jual',
        'harga_modul_regular',
        'harga_modul_regular_dollar',
        'kurs_dollar',
        'biaya_modul_regular',
        'biaya_modul_regular_dollar',
        'makan_siang',
        'coffee_break',
        'konsumsi',
        'souvenir_satu',
        'souvenir',
        'pa_hotel_akomodasi',
        'pa_hotel',
        'total_pa_hotel',
        'exam',
        'pc_pax',
        'pc_instruktur',
        'konsumsi_instruktur',
        'pc',
        'alat',
        'fee_instruktur',
        'total_fee_instruktur',
        'nett_penjualan',
        'komentar',
    ];

    // Relationship to `RKM`
    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }

    // Relationship to `analisisrkmmingguan`
    public function analisisrkmmingguan()
    {
        return $this->hasMany(analisisrkmmingguan::class, 'id_kelasanalisis', 'id');
    }
}

