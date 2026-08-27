<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modul extends Model
{
    use HasFactory;
    protected $fillable = [
        'no_modul',
        'id_materi',
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

    // 2. Buat Accessor khusus untuk menangani fallback
    public function getDetailMateriAttribute()
    {
        // Prioritas 1: Jika id_materi ada, langsung cari berdasarkan ID (lebih cepat)
        if (!empty($this->id_materi)) {
            $materi = Materi::find($this->id_materi);
            if ($materi) {
                return $materi;
            }
        }

        // Prioritas 2 & 3: Jika id_materi kosong, cari berdasarkan nama atau kode
        return Materi::where(function ($query) {
            if (!empty($this->nama_materi)) {
                $query->where('nama_materi', $this->nama_materi);
            }
            if (!empty($this->kode_materi)) {
                $query->orWhere('kode_materi', $this->kode_materi);
            }
        })->first();
    }
}
