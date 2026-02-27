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
        'id_peserta',
        'id_peluang',
        'aktivitas',
        'subject',
        'deskripsi',
        'waktu_aktivitas',
        'harga',
        'total',
        'pax',
        'foto_lokasi',
        'longitude',
        'latitude',
    ];

    public function peluang()
    {
        return $this->belongsTo(Peluang::class, 'id_peluang', 'id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'id_contact');
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'id_peserta');
    }
    
        public function target()
    {
        return $this->belongsTo(TargetActivity::class, 'id_sales', 'id_sales');
    }
}
