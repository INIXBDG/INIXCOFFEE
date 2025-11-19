<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingPengajuanSouvenir extends Model
{
    use HasFactory;

    protected $fillable = ['id_pengajuan_lab_subs', 'tracking', 'tanggal'];
}
