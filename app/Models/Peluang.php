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
        'materi',
        'catatan',
        'harga',
        'netsales',
        'periode_mulai',
        'periode_selesai',
        'pax',
        'final',
        'biru',
        'merah',
        'tahap',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'id_contact', 'id');
    }

    public function aktivitas()
    {
        return $this->hasMany(Aktivitas::class, 'id_peluang', 'id');
    }
}
