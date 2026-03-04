<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianExam extends Model
{
    use HasFactory;

    protected $table = 'penilaian_exams';

    protected $fillable = [
        'id_rkm',
        'nilai_emote',
    ];

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }
}
