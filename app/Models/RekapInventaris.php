<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RekapInventaris extends Model
{
    use HasFactory;

    protected $table = 'recap_inventaris';

    protected $fillable = [
        'idbarang',
        'name',
        'kategori',
        'qty',
        'total_harga',
        'waktu_pembelian',
        'ruangan',
        'no_kk',
        'deskripsi'
    ];
}