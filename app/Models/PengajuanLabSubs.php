<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanLabSubs extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_lab_subs'; // Pastikan nama tabel sesuai

    protected $fillable = [
        'kode_karyawan',
        'id_labs',
        'id_rkm',
        'id_tracking',
        'jenis_transaksi',
        'invoice',
        'lab_snapshot',
    ];

    protected $casts = [
        'lab_snapshot' => 'array',
    ];

    public function lab()
    {
        return $this->belongsTo(Lab::class, 'id_labs');
    }

    public function tracking()
    {
        return $this->hasMany(TrackingPengajuanLabSubs::class, 'id_pengajuan_lab_subs');
    }

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm');
    }

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'kode_karyawan', 'kode_karyawan');
    }
}
