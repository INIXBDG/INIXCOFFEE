<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_perusahaan',
        'kategori_perusahaan',
        'lokasi',
        'sales_key',
        'status',
        'npwp',
        'alamat',
        'cp',
        'no_telp',
        'foto_npwp',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'sales_key', 'kode_karyawan');
    }

    public function rkms()
    {
        return $this->hasMany(Rkm::class, 'perusahaan_key', 'id');
    }

    public function peserta()
    {
        return $this->hasMany(Peserta::class, 'perusahaan_key', 'id');
    }
}
