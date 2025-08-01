<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aktivitas extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_sales',
        'id_contact',
        'id_peluang',
        'aktivitas',
        'subject',
        'deskripsi',
        'waktu_aktivitas',
    ];

    public function peluang()
    {
        return $this->belongsTo(Peluang::class, 'id_peluang', 'id');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_contact', 'id');
    }

    
}
