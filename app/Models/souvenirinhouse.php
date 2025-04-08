<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class souvenirinhouse extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_souvenir',
        'id_rkm',
    ];

    public function souvenir()
    {
        return $this->belongsTo(souvenir::class, 'nama_souvenir', 'id');
    }

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }
}
