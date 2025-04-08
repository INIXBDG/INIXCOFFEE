<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tracking_pengajuan_barang extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_pengajuan_barang',
        'tracking',
        'tanggal',
    ];
    public function pengajuanbarang()
    {
        return $this->hasOne(PengajuanBarang::class, 'id', 'id_pengajuan_barang');
    }
}
