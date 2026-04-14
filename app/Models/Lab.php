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
        'merk',
        'tipe',
        'desc',
        'lab_url',
        'access_code',
        'duration_minutes',
        'mata_uang',
        'harga',
        'kurs',
        'harga_rupiah',
        'start_date',
        'end_date',
        'status',
        'is_active'
    ];

    public function scopeAvailableSubscription($query)
    {
        return $query->where('tipe', 'subscription')
                     ->where('is_active', true)
                     ->where('status', 'Active')
                     ->where('end_date', '>=', now());
    }

    public function materis()
    {
        return $this->belongsToMany(Materi::class, 'lab_materi', 'lab_id', 'materi_id');
    }
}
