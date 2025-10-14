<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'kode_karyawan',
        'nama_subs',
        'merk',
        'desc',
        'subs_url',
        'access_code',
        'mata_uang',
        'harga',
        'start_date',
        'end_date',
        'status',
        'kurs',
        'harga_rupiah',
    ];
}
