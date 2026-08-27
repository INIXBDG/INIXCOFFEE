<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori',
        'divisi',
        'teknisi',
        'nama_barang',
        'tanggal_mulai',
        'tanggal_selesai',
        'no_voucher',
        'biaya',
        'keterangan',
        'status',
    ];
}
