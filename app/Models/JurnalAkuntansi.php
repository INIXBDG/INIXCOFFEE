<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurnalAkuntansi extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'jurnal_akuntansis';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'id_pengajuan_barang',
        'tanggal_transaksi',
        'keterangan',
        'debit',
        'kredit',
    ];

    /**
     * Relasi ke model PengajuanBarang.
     */
    public function pengajuanBarang()
    {
        return $this->belongsTo(PengajuanBarang::class, 'id_pengajuan_barang', 'id');
    }
}