<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class hasilexam extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_registexam',
        'id_peserta',
        'hasil',
        'pdf',
        'keterangan',
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'id_peserta', 'id');
    }

    public function exam()
    {
        return $this->belongsTo(Eksam::class, 'id_exam', 'id');
    }

    public function registexam()
    {
        return $this->belongsTo(registexam::class, 'id_registexam', 'id');
    }

}
