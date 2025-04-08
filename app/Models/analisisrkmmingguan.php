<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class analisisrkmmingguan extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id_kelasanalisis',
        'nama_materi',
        'nett_penjualan',
        'fixcost',
        'profit',
        'tahun',
        'bulan',
        'minggu',
    ];

    // Relationship to `kelasanalisis`
    public function kelasanalisis()
    {
        return $this->belongsTo(kelasanalisis::class, 'id_kelasanalisis', 'id');
    }
}

