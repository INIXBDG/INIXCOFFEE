<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class perhitunganNetSales extends Model
{
    use HasFactory;

    protected $fillable = ['id_rkm', 'transportasi', 'penginapan', 'fresh_money', 'entertaint', 'souvenir', 'harga_penawaran', 'tgl_pa', 'tipe_pembayaran', 'pajak'];

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }
    public function aprovedNetSales()
    {
        return $this->hasMany(AprovedNetSales::class, 'id_netSales', 'id');
    }    
}
