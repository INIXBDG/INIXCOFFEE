<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class trackingTagihanPerusahaan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_tagihan_perusahaan',
        'nominal',
        'tracking',
        'status',
        'tanggal_selesai',
        'keterangan'
    ];

    public function tagihanPerusahaan ()
    {
        return $this->belongsTo(tagihanPerusahaan::class, 'id_tagihan_perusahaan');
    }
}
