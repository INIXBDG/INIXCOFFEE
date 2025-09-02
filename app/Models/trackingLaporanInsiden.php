<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class trackingLaporanInsiden extends Model
{
    use HasFactory;

    protected $fillable = ['id_laporanInsiden', 'responder', 'status', 'detail'];

    public function responder()
    {
        return $this->belongsTo(karyawan::class, 'id', 'responder');
    }

    public function laporanInsiden() 
    {
        return $this->belongsTo(laporanInsiden::class, 'id', 'id_laporanInsiden');
    }
}
