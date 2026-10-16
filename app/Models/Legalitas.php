<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Legalitas extends Model
{
    use HasFactory;
    protected $table = 'legalitas';
    protected $fillable = [
        'nama_dokumen',
        'path_dokumen',
        'nomor_dokumen',
        'tanggal_terbit',
        'masa_berlaku',
        'satuan_masa_berlaku',
        'tanggal_berakhir',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_berakhir' => 'date',
    ];
}
