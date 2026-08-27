<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingPembelianHr extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_pembelian',
        'tracking',
        'id_karyawan',
    ];

    public function pembelian()
    {
        return $this->belongsTo(PembelianHr::class, 'id_pembelian', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan', 'id');
    }
}
