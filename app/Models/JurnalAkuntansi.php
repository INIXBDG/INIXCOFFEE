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
        'nomor_kk',
        'id_pengajuan_barang',
        'id_perhitungan_net_sales',
        'tanggal_transaksi',
        'keterangan',
        'no_akun',
        'debit',
        'kredit',
    ];

    protected $casts = [
        'no_akun' => 'string',
    ];

    /**
     * Relasi ke model PengajuanBarang.
     */
    public function pengajuanBarang()
    {
        return $this->belongsTo(PengajuanBarang::class, 'id_pengajuan_barang', 'id');
    }

    public function netSales()
    {
        return $this->belongsTo(perhitunganNetSales::class, 'id_perhitungan_net_sales', 'id');
    }
    public function no_accounting()
    {
        return $this->belongsTo(no_akun::class, 'no_akun', 'no');
    }


}