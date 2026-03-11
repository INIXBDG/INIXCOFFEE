<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SurveyKepuasan extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'id_user',
        'ticket_id',
        'q1',
        'q2',
        'q3',
        'q4',
        'q5',
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_user', 'id');
    }
}
