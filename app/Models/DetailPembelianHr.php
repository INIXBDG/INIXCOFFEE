<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPembelianHr extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_pembelian',
        'nama_barang',
        'kategori',
        'qty',
        'harga',
        'keterangan',
    ];

    public function pembelian()
    {
        return $this->belongsTo(PembelianHr::class, 'id_pembelian', 'id');
    }
}
