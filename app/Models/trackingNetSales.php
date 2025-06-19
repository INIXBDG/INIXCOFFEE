<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class trackingNetSales extends Model
{
    use HasFactory;

    protected $fillable = ['id_netSales', 'tracking'];

    public function perhitunganNetSales()
    {
        return $this->belongsTo(perhitunganNetSales::class, 'id_netSales', 'id');
    }
}
