<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatatanClientSales extends Model
{
    use HasFactory;

    protected $fillable = [
        'laporan_id',
        'nama_perusahaan',
        'kebutuhan',
        'rekomendasi_silabus',
        'catatan',
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanHarianSales::class, 'laporan_id');
    }
}
