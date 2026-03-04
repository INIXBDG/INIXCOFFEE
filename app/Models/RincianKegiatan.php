<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RincianKegiatan extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_kegiatan',
        'hal',
        'rincian',
        'qty',
        'harga_satuan',
        'total',
    ];

    public function kegiatan(){
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan', 'id');
    }
}
