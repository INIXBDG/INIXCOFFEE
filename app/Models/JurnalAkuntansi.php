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
        'id_surat_perjalanan',
        'tanggal_transaksi',
        'keterangan',
        'no_akun',
        'debit',
        'kredit',
    ];

    protected $casts = [
        'no_akun' => 'string',
        'id_pengajuan_barang' => 'array',
    ];

    /**
     * Relasi ke model PengajuanBarang.
     */
    public function pengajuanBarang()
    {
        return $this->belongsTo(PengajuanBarang::class, 'id_pengajuan_barang', 'id');
    }

    // Function baru ListPengajuan untuk mengambil data pengajuan berdasarkan array ID dari jurnal akuntansi || yg pengajuan barang diatas ga kepake, dibiarkan dulu
    public function ListPengajuan()
    {
        if (!$this->id_pengajuan_barang || !is_array($this->id_pengajuan_barang)) {
            return collect();
        }

        return PengajuanBarang::with(['detail', 'tracking', 'karyawan'])
            ->whereIn('id', $this->id_pengajuan_barang)
            ->get();
    }

    public function netSales()
    {
        return $this->belongsTo(perhitunganNetSales::class, 'id_perhitungan_net_sales', 'id');
    }
    public function no_accounting()
    {
        return $this->belongsTo(no_akun::class, 'no_akun', 'no');
    }

    public function spj()
    {
        return $this->belongsTo(SuratPerjalanan::class, 'id_surat_perjalanan', 'id');
    }


}