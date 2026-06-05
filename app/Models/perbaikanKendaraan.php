<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\tracking_pengajuan_barang;
use App\Models\PengajuanBarang;

class PerbaikanKendaraan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_kondisi_kendaraan',
        'kendaraan',
        'id_user',
        'type_condition',
        'type_vehicle_condition',
        'type_repair',
        'deskripsi_kondisi',
        'tanggal_kejadian',
        'waktu_kejadian',
        'lokasi',
        'estimasi',
        'harga_akhir',
        'status',
        'bukti',
        'tanggal_perbaikan',
        'selesai_perbaikan',
        'detail_perbaikan',
        'document',
        'invoice',
        'deskripsi_perbaikan',
        'id_vendor',
        'pengajuanbarangs_id'
    ];

    protected $casts = [
        'estimasi' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(vendorBengkel::class, 'id_vendor', 'id');
    }

    public function pengajuanBarang()
    {
        return $this->belongsTo(PengajuanBarang::class, 'pengajuanbarangs_id');
    }

    public function getStatusAttribute($value)
    {
        if (empty($this->pengajuanbarangs_id)) {
            return $value;
        }

        $latestTracking = tracking_pengajuan_barang::where('id_pengajuan_barang', $this->pengajuanbarangs_id)
            ->latest('id')
            ->first();

        return $latestTracking ? $latestTracking->tracking : $value;
    }
}