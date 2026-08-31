<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanSubs extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_subs';

    protected $fillable = [
        'kode_karyawan',
        'id_subs',
        'id_rkm',
        'id_tracking',
        'jenis_transaksi',
        'invoice',
        'subs_snapshot',
    ];

    protected $casts = [
        'subs_snapshot' => 'array',
    ];

    public function subs()
    {
        return $this->belongsTo(Subscription::class, 'id_subs');
    }

    public function tracking()
    {
        return $this->hasMany(TrackingPengajuanSubs::class, 'id_pengajuan_subs');
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
