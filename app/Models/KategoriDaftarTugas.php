<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriDaftarTugas extends Model
{
    use HasFactory;

    protected $fillable = ['Jabatan_Pembuat', 'Tipe', 'judul_kategori', 'id_user'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_user', 'id');
    }
}
