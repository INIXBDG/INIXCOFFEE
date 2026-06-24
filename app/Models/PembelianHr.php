<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianHr extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_kk',
        'status_pembelian',
        'tanggal_pembelian',
        'invoice',
    ];

    public function details()
    {
        return $this->hasMany(DetailPembelianHr::class, 'id_pembelian', 'id');
    }

    public function tracking()
    {
        return $this->hasMany(TrackingPembelianHr::class, 'id_pembelian', 'id');
    }
}
