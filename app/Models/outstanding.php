<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class outstanding extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_rkm',
        'net_sales',
        'status_pembayaran',
        'due_date',
        'pic',
        'sales_key',
        'no_invoice',
        'no_regist',
        'tanggal_bayar',
    ];

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }

    public function tracking_outstanding()  
    {  
        return $this->hasOne(trackingOutstanding::class, 'id_outstanding', 'id');  
    }  

    // Cek apakah status pembayaran 0 dan due_date belum lewat
    public function requiresNotification()
    {
        return $this->status_pembayaran == 0 && Carbon::now()->lt(Carbon::parse($this->due_date));
    }
}
