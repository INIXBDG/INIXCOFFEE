<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanLabSubs extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_karyawan',
        'id_labs',
        'id_subs',
        'id_rkm',
        'id_tracking',
        'invoice',
        'lab_snapshot',
        'subs_snapshot',
    ];

    protected $casts = [
        'lab_snapshot' => 'array',
        'subs_snapshot' => 'array',
    ];

    public function tracking()
    {
        return $this->hasMany(TrackingPengajuanLabSubs::class, 'id_pengajuan_lab_subs');
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class, 'id_labs');
    }

    public function subs()
    {
        return $this->belongsTo(Subscription::class, 'id_subs');
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
