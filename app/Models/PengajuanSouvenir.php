<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanSouvenir extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_souvenirs';

    /**
     * Atribut yang dapat diisi (fillable)
     * Kolom id_souvenir, pax, harga_satuan, harga_total TELAH DIHAPUS
     */
    protected $fillable = [
        'id_karyawan',
        'id_vendor',
        'id_tracking',
        'total_keseluruhan',
        'invoice',
    ];

    /**
     * Tipe data untuk atribut.
     */
    protected $casts = [
        'total_keseluruhan' => 'decimal:2',
    ];

    /**
     * Relasi ke karyawan yang mengajukan.
     */
    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan', 'id');
    }

    /**
     * Relasi ke Vendor.
     */
    public function vendor()
    {
        return $this->belongsTo(vendorSouvenir::class, 'id_vendor', 'id');
    }

    /**
     * Relasi ke status tracking TERBARU.
     */
    public function tracking()
    {
        return $this->belongsTo(TrackingPengajuanSouvenir::class, 'id_tracking', 'id');
    }

    /**
     * Relasi ke SEMUA histori tracking.
     */
    public function history()
    {
        return $this->hasMany(TrackingPengajuanSouvenir::class, 'id_pengajuan_souvenir', 'id')->orderBy('created_at', 'asc');
    }

    /**
     * RELASI KUNCI: Satu Pengajuan memiliki BANYAK Detail Item
     */
    public function detail()
    {
        return $this->hasMany(DetailPengajuanSouvenir::class, 'id_pengajuan_souvenir', 'id');
    }
}
