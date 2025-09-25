<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class trackingNetSales extends Model
{
    use HasFactory;

    protected $fillable = ['id_rkm', 'tracking'];

    public function perhitunganNetSales()
    {
        return $this->hasMany(perhitunganNetSales::class, 'id_tracking', 'id');
    }
}
