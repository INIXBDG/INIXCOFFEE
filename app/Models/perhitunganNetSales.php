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
    public function approvedNetSales()
    {
        return $this->hasMany(approvedNetSales::class, 'id_netSales', 'id');
    }    
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan'); // sesuaikan 'id_karyawan' dengan nama kolom foreign key yang benar
    }

}
