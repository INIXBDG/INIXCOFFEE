<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RKM extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = [
        'sales_key',
        'materi_key',
        'perusahaan_key',
        'harga_jual',
        'pax',
        'tanggal_awal',
        'tanggal_akhir',
        'metode_kelas',
        'event',
        'ruang',
        'instruktur_key',
        'instruktur_key2',
        'asisten_key',
        'status',
        'exam',
        'authorize',
        'registrasi_form',
        'quartal',
        'bulan',
        'tahun',
        'isi_pax',
        'makanan',
        'pdf_peserta'
    ];
    protected $dates = ['tanggal_awal', 'tanggal_akhir'];

    public function perhitunganNetSales()
    {
        return $this->hasMany(perhitunganNetSales::class, 'id_rkm', 'id');
    }

    public function outstanding()
    {
        return $this->hasOne(outstanding::class, 'id_rkm', 'id');
    }


    public function sales()
    {
        return $this->belongsTo(karyawan::class, 'sales_key', 'kode_karyawan');
    }

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_key', 'id');
    }

    public function instruktur()
    {
        return $this->belongsTo(Karyawan::class, 'instruktur_key', 'kode_karyawan');
    }

    public function instruktur2()
    {
        return $this->belongsTo(Karyawan::class, 'instruktur_key2', 'kode_karyawan');
    }

    public function asisten()
    {
        return $this->belongsTo(Karyawan::class, 'asisten_key', 'kode_karyawan');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_key', 'id');
    }

    public function comments()
    {
        return $this->hasMany(comment::class, 'rkm_key', 'id');
    }

    public function rekomendasilanjutan()
    {
        return $this->hasOne(RekomendasiLanjutan::class, 'id_rkm', 'id');
    }

    public function exam()
    {
        return $this->hasOne(eksam::class, 'id_rkm');
    }

    public function analisisrkm()
    {
        return $this->hasOne(kelasanalisis::class, 'id_rkm');
    }

    public function souvenirpeserta()
    {
        return $this->hasMany(souvenirpeserta::class, 'id_rkm', 'id');
    }

    public function registrasi()
    {
        return $this->hasMany(Registrasi::class, 'id_rkm', 'id');
    }

    public function nilaifeedback()
    {
        return $this->hasMany(nilaifeedback::class, 'id_rkm', 'id');
    }

    public function sertifikatPDF()
    {
        return $this->hasMany(SertifikatPDF::class, 'id_rkm', 'id');
    }

    public function absensiPDF()
    {
        return $this->hasOne(absensiPDF::class, 'id_rkm', 'id');
    }

    public function peluang()
    {
        return $this->hasOne(Peluang::class, 'id_rkm', 'id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'id_rkm');
    }
    public function kwitansi()
    {
        return $this->hasMany(Kwitansi::class, 'id_rkm');
    }

    public function checklistKeperluan()
    {
        return $this->hasOne(ChecklistKeperluan::class, 'id_rkm', 'id');
    }
}
