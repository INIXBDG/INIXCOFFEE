<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    use HasFactory;

    protected $table = 'labs';

    protected $fillable = [
        'kode_karyawan',
        'nama_labs',
        'desc',
        'lab_url',
        'access_code',
        'duration_minutes',
        'mata_uang',
        'harga',
        'start_date',
        'end_date',
        'status',
        'kurs',
        'harga_rupiah',

    ];
}
