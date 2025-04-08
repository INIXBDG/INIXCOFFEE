<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class comment extends Model
{
    protected $fillable = [
        'karyawan_key',
        'content',
        'rkm_key',
        'materi_key',
    ];

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'rkm_key', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_key', 'id');
    }
}
