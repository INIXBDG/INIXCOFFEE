<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaTransportasiDriver extends Model
{
    use HasFactory;

    protected $fillable = ['id_karyawan', 'id_pengajuan_barang', 'tipe', 'harga', 'bukti', 'keterangan', 'id_pickup_driver'];

    public function PengajuanBarang()
    {
        return $this->belongsTo(PengajuanBarang::class, 'id_pengajuan_barang', 'id');
    }

    public function Karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan', 'id');
    }

    public function pickupDriver()
    {
        return $this->belongsTo(pickupDriver::class, 'id_pickup_driver');
    }
}
