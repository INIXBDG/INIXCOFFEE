<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Maatwebsite\Excel\Concerns\Exportable;
class Nilaifeedback extends Model
{
    use HasFactory;
    // use Exportable;
    protected $fillable = [
        'id_regist',
        'id_rkm',
        'email',
            'M1',
            'M2',
            'M3',
            'M4',
            'P1',
            'P2',
            'P3',
            'P4',
            'P5',
            'P6',
            'P7',
            'F1',
            'F2',
            'F3',
            'F4',
            'F5',
            'I1',
            'I2',
            'I3',
            'I4',
            'I5',
            'I6',
            'I7',
            'I8',
            'I1b',
            'I2b',
            'I3b',
            'I4b',
            'I5b',
            'I6b',
            'I7b',
            'I8b',
            'I1as',
            'I2as',
            'I3as',
            'I4as',
            'I5as',
            'I6as',
            'I7as',
            'I8as',
            'U1',
            'U2',
    ];
    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm');
    }

    public function regist()
    {
        return $this->belongsTo(Registrasi::class, 'id_regist', 'id');
    }
}
