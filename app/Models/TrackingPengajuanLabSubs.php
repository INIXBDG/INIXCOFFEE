<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingPengajuanLabSubs extends Model
{
    use HasFactory;

    protected $fillable = ['id_pengajuan_lab_subs', 'tracking', 'tanggal'];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanLabSubs::class, 'id_pengajuan_lab_subs');
    }
}
