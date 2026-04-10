<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catering extends Model
{
    use HasFactory;

    protected $fillable = ['id_karyawan', 'tipe', 'invoice', 'status_pembelian', 'tanggal_pembelian'];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan');
    }

    public function DetailCatering()
    {
        return $this->hasMany(DetailCatering::class, 'id_catering');
    }

    public function TrackingCatering()
    {
        return $this->hasMany(TrackingCatering::class, 'id_catering', 'id');
    }
}
