<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peluang extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_contact',
        'id_sales',
        'id_contact',
        'judul',
        'deskripsi',
        'jumlah',
        'tahap',
        'probabilitas',
        'tanggal_tutup_diharapkan',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'id_contact', 'id');
    }

    public function aktivitass()
    {
        return $this->hasMany(Aktivitas::class, 'id_peluang', 'id');
    }
}
