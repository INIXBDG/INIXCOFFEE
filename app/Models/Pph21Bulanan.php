<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pph21Bulanan extends Model
{
    use HasFactory;

    protected $table = 'pph21_bulanans';

    protected $fillable = ['karyawan_id', 'bulan', 'tahun', 'total_pph', 'sumber', 'created_by'];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'total_pph' => 'integer',
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'karyawan_id');
    }
}