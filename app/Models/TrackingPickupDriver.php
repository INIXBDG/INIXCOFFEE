<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingPickupDriver extends Model
{
    use HasFactory;
    protected $fillable = ['pickup_driver_id', 'status', 'diubah_oleh'];
}
