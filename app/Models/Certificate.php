<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_sertifikat',
        'rkm_id',
        'id_peserta',
        'nama_peserta',
        'nama_materi',
        'tanggal_awal',
        'tanggal_akhir',
        'tanggal_pelatihan',
        'ttd_id',
        'pdf_path'
    ];

    protected $casts = [
        'tanggal_awal' => 'date',
        'tanggal_akhir' => 'date'
    ];

    // Relasi ke RKM
    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'rkm_id');
    }

    // Relasi ke Peserta (Karyawan)
    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'id_peserta');
    }

    // Relasi ke TTD (Karyawan ID 4)
    public function penandatangan()
    {
        return $this->belongsTo(Karyawan::class, 'ttd_id');
    }

    // // Generate nomor sertifikat otomatis
    // public static function generateNomorSertifikat()
    // {
    //     $year = date('Y');
    //     $month = date('m');
        
    //     // Format: CERT/2025/01/0001
    //     $lastCert = self::whereYear('created_at', $year)
    //         ->whereMonth('created_at', $month)
    //         ->orderBy('id', 'desc')
    //         ->first();
        
    //     $number = $lastCert ? ((int) substr($lastCert->nomor_sertifikat, -4)) + 1 : 1;
        
    //     return sprintf('CERT/%s/%s/%04d', $year, $month, $number);
    // }
//     public static function generateNomorSertifikat()
// {
//     $year = date('Y');
//     $last = self::whereYear('created_at', $year)->count();
//     $number = str_pad($last + 1, 4, '0', STR_PAD_LEFT);
//     return "CERT-{$year}-{$number}";
// }

    // // Format nomor sertifikat untuk tampilan (6 digit)
    // public function getFormattedNomorAttribute()
    // {
    //     // Ambil 4 digit terakhir dan format jadi 6 digit
    //     $number = (int) substr($this->nomor_sertifikat, -4);
    //     return sprintf('%06d', $number);
    // }
}