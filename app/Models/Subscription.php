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
        'tipe',
        'desc',
        'subs_url',
        'access_code',
        'duration_minutes',
        'mata_uang',
        'harga',
        'kurs',
        'harga_rupiah',
        'start_date',
        'end_date',
        'status',
        'is_active',
    ];

    public function materis()
    {
        return $this->belongsToMany(Materi::class, 'subscription_materi', 'subscription_id', 'materi_id');
    }
}
