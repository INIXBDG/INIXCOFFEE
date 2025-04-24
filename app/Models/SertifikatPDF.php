<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SertifikatPDF extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_rkm',
        'pdf_path',
        'pdf_name',
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'id_peserta');
    }

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm');
    }
}
