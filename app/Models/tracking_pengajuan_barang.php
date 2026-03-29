<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tracking_pengajuan_barang extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'id_pengajuan_barang',
        'tracking',
        'detail_perubahan',
        'tanggal',
    ];

    /**
     * Tipe data untuk atribut yang didefinisikan.
     *
     * @var array
     */
    protected $casts = [
        'detail_perubahan' => 'array',
    ];

    public function pengajuanbarang()
    {
        return $this->hasOne(PengajuanBarang::class, 'id', 'id_pengajuan_barang');
    }
}