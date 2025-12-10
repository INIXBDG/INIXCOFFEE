<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class laporanInsiden extends Model
{
    use HasFactory;

    protected $fillable = ['pelapor', 'kategori', 'deskripsi', 'tanggal_kejadian', 'waktu_kejadian', 'lampiran', 'status', 'catatan'];

    public function Pelapor()
    {
        return $this->belongsTo(karyawan::class, 'pelapor', 'id');
    }

    public function tracking()
    {
        return $this->hasMany(trackingLaporanInsiden::class, 'id_laporanInsiden', 'id');
    }
}
