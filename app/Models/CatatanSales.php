<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatatanSales extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_aktivitas',
        'id_sales',
        'catatan',
    ];

    public function aktivitas()
    {
        return $this->belongsTo(Aktivitas::class, 'id_aktivitas', 'id');
    }
}
