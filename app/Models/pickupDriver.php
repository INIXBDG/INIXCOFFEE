<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pickupDriver extends Model
{
    use HasFactory;
    protected $fillable = ['id_karyawan', 'id_pembuat', 'status_apply', 'waktu_kepulangan', 'status_driver', 'kendaraan', 'budget'];

    public function detailPickupDriver()
    {
        return $this->hasMany(DetailPickupDriver::class, 'pickup_driver_id', 'id');
    }

    public function Tracking()
    {
        return $this->hasMany(TrackingPickupDriver::class, 'pickup_driver_id', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id');
    }

    public function pembuat()
    {
        return $this->belongsTo(Karyawan::class, 'id_pembuat', 'id');
    }

    public function biayaTransportasi()
    {
        return $this->hasMany(BiayaTransportasiDriver::class, 'id_pickup_driver');
    }
}
