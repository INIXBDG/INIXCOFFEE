<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdministrasiKaryawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_administrasi',
        'status',
        'dateline',
        'tanggal_selesai',
        'keterangan',
        'bukti_transfer'
    ];
}
