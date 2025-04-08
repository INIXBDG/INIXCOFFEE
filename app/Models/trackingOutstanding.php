<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class trackingOutstanding extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_outstanding',
        'invoice',
        'faktur_pajak',
        'dokumen_tambahan',
        'konfir_cs',
        'tracking_dokumen',
        'no_resi',
        'konfir_pic',
        'pembayaran',
        'status_resi',
        'status_pic',
    ];

    public function outstanding()  
    {  
        return $this->belongsTo(outstanding::class, 'id_outstanding', 'id');  
    }  
}
