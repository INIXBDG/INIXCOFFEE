<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanBarang extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'pengajuanbarangs';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'id_karyawan',
        'id_kegiatan',
        'id_tracking',
        'tipe',
        'invoice',
        'no_kk',
        'tanggal_pencairan',
        'tanggal_terima_finance',

    ];

    /**
     * Tipe data untuk atribut yang didefinisikan.
     *
     * @var array
     */
    // protected $casts = [
    //     'harga_barang' => 'decimal:2',
    //     'approval_manager' => 'string',
    //     'approval_hrd' => 'string',
    //     'approval_direksi' => 'string',
    // ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan', 'id');
    }
    public function tracking()
    {
        return $this->belongsTo(tracking_pengajuan_barang::class, 'id_tracking', 'id');
    }
    public function detail()
    {
        return $this->hasMany(detailPengajuanBarang::class, 'id_pengajuan_barang', 'id');
    }

    public function pelatihan()
    {
        return $this->hasOne(Pelatihan::class, 'id_pengajuan_barang');
    }

    public function sertifikasi()
    {
        return $this->hasOne(Sertifikasi::class, 'id_pengajuan_barang');
    }

    public function jurnalAkuntansi()
    {
        return $this->hasOne(JurnalAkuntansi::class, 'id_pengajuan_barang', 'id');
    }

    public function perbaikanKendaraan()
    {
        return $this->hasOne(perbaikanKendaraan::class, 'pengajuanbarangs_id');
    }

}
