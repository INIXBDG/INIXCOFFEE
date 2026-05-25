<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'status',
        'bukti',
        'tanggal_perbaikan',
        'selesai_perbaikan',
        'detail_perbaikan',
        'document',
        'invoice',
        'deskripsi_perbaikan',
        'id_vendor'
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

     public function getSyncedStatus()
    {
        if (!$this->pengajuanbarangs_id) {
            return $this->status;
        }

        $latestTracking = \App\Models\tracking_pengajuan_barang::where('id_pengajuan_barang', $this->pengajuanbarangs_id)
            ->latest('id') 
            ->first();

        return $latestTracking ? $latestTracking->tracking : $this->status;
    }

    public function syncStatusFromPengajuan()
    {
        $syncedStatus = $this->getSyncedStatus();
        
        if ($syncedStatus !== $this->status) {
            $this->update(['status' => $syncedStatus]);
            return true;
        }
        return false;
    }
}