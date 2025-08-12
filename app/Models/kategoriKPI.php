<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kategoriKPI extends Model
{
    use HasFactory;
    protected $table = 'kategori_k_p_i_s';

    protected $fillable = ['judul_kategori', 'tipe_kategori', 'level', 'kode_kategori', 'bobot'];

    public function formPenilaian()
    {
        return $this->belongsTo(formPenilaian::class, 'kode_kategori', 'kode_kategori');
    }

    public function tipeKategoriTabels()
    {
        return $this->hasMany(tipeKategoriTabel::class, 'id_kategori', 'id');
    }

    public function nilaiKPI()
    {
        return $this->hasOne(NilaiKPI::class, 'id_kategori', 'id');
    }
}
