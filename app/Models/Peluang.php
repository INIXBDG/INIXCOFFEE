<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peluang extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_contact',
        'id_sales',
        'id_rkm',
        'materi', // This is assumed to be the foreign key for Materi
        'catatan',
        'harga',
        'netsales',
        'periode_mulai',
        'periode_selesai',
        'pax',
        'final',
        'biru',
        'merah',
        'tahap',
    ];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_contact', 'id');
    }

    public function aktivitas()
    {
        return $this->hasMany(Aktivitas::class, 'id_peluang', 'id');
    }

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }

    // Add the relationship to Materi
    public function materiRelation()
    {
        return $this->belongsTo(Materi::class, 'materi', 'id');
    }
}