<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoExamSertifa extends Model
{
    use HasFactory;

    protected $table = 'po_exam_sertifa';

    protected $fillable = [
        'id_materi',
        'id_rkm',
        'tanggal_exam',
        'id_perusahaan',
        'pax',
        'harga',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'id_materi');
    }

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }
}
