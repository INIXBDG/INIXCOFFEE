<?php

namespace App\Models;

use App\Notifications\rkmnewNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class netSales extends Model
{
    use HasFactory;
    protected $fillable = ['id_rkm', 'sebelumNetSales', 'pajak', 'cashback', 'biaya_akomodasi', 'entertaint'];

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }
}
