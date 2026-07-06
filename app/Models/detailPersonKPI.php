<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detailPersonKPI extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_target',
        'detailTargetKey',
        'id_karyawan',
        'presentase_kemampuan',
        'presentase_standar',
    ];

    public function detailTargetKPI()
    {
        return $this->belongsTo(DetailTargetKPI::class, 'detailTargetKey', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan', 'id');
    }
}
