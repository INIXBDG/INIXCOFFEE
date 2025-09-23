<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class approvedNetSales extends Model
{
    use HasFactory;

    protected $fillable = ['id_rkm', 'status', 'level_status', 'keterangan', 'tanggal'];

    public function perhitunganNetSales()
    {
        return $this->hasOne(perhitunganNetSales::class, 'id_rkm', 'id_rkm');
    }
}
