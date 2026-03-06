<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tagihanPerusahaan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kegiatan',
        'nominal',
        'tipe',
        'tanggal_perkiraan_mulai',
        'tanggal_perkiraan_selesai',
        'last_generate',
    ];

    public function trackingTagihan()
    {
        return $this->hasMany(trackingTagihanPerusahaan::class, 'id_tagihan_perusahaan');
    }
}
