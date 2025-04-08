<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class souvenirpeserta extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_souvenir',
        'id_rkm',
        'id_regist',
    ];

    public function souvenir()
    {
        // return $this->belongsTo(souvenir::class, 'id_souvenir', 'id');
        return $this->hasOne(souvenir::class, 'id', 'id_souvenir');
    }

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }

    public function regist()
    {
        return $this->belongsTo(Registrasi::class, 'id_regist', 'id');
    }
}
