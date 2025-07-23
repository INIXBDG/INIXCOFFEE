<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tipeKategoriTabel extends Model
{
    use HasFactory;
    
    protected $table = 'tipe_kategori_tabels';

    protected $fillable = ['id_kategori', 'ket_tipe', 'nilai_ket_tipe'];

    public function kategoriKPI()
    {
        return $this->belongsTo(kategoriKPI::class, 'id_kategori');
    }
}
