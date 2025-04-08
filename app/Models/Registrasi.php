<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registrasi extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_rkm',
        'id_peserta',
        'id_materi',
        'id_instruktur',
        'id_sales',
    ];

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'id_peserta', 'id');
    }

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'id_materi', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_instruktur', 'kode_karyawan');
    }

    public function sales()
    {
        return $this->belongsTo(karyawan::class, 'id_sales', 'kode_karyawan');
    }

    // Tambahkan relasi dengan model SouvenirPeserta
    public function souvenirpeserta()
    {
        return $this->hasOne(SouvenirPeserta::class, 'id_regist', 'id');
    }
}

