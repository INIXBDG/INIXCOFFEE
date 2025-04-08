<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detailPengajuanBarang extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_pengajuan_barang',
        'nama_barang',
        'qty',
        'harga',
        'keterangan',
    ];

    public function detail()
    {
        return $this->hasMany(PengajuanBarang::class, 'id', 'id_pengajuan_barang');
    }
}
