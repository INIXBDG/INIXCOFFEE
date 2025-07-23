<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class formPenilaian extends Model
{
    use HasFactory;

    protected $fillable = ['nama_penilaian', 'id_karyawan', 'kode_kategori', 'kode_form', 'quartal', 'tahun'];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan');
    }

    public function kategoriKPI()
    {
        return $this->belongsTo(kategoriKPI::class, 'id_kategori');
    }

    public function evaluator()
    {
        return $this->belongsTo(karyawan::class, 'id_evaluator');
    }

    public function evaluated()
    {
        return $this->belongsTo(karyawan::class, 'id_evaluated');
    }
}
