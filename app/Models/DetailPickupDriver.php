<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPickupDriver extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_driver_id',
        'tipe',
        'lokasi',
        'tanggal_keberangkatan',
        'waktu_keberangkatan',
        'detail'
    ];
}
