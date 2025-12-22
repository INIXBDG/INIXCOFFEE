<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modul extends Model
{
    use HasFactory;
    protected $fillable = [
        'no_modul',
        'kode_materi',
        'nama_materi',
        'awal_training',
        'akhir_training',
        'jumlah',
        'harga_satuan',
        'total',
    ];

    public function nomorModul()
    {
        return $this->belongsTo(NomorModul::class, 'no_modul', 'id');
    }

    public function pesertaModul()
    {
        return $this->hasMany(PesertaModul::class, 'modul', 'id');
    }
}
