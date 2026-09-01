<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingPengajuanSubs extends Model
{
    use HasFactory;

    protected $table = 'tracking_pengajuan_subs';

    protected $fillable = [
        'id_pengajuan_subs',
        'tracking',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanSubs::class, 'id_pengajuan_subs');
    }
}
