<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RKM extends Model
{
    use HasFactory;

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
        'isi_pax'
    ];

    public function perhitunganNetSales()
    {
        return $this->hasOne(perhitunganNetSales::class, 'id_rkm', 'id');
    }    

    public function sales()
    {
        return $this->belongsTo(Karyawan::class, 'sales_key', 'kode_karyawan');
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
        return $this->hasMany(Comment::class, 'rkm_key', 'id');
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

    public function sertifikatPDF(){
        return $this->hasMany(SertifikatPDF::class, 'id_rkm', 'id');
    }

    public function absensiPDF(){
        return $this->hasOne(absensiPDF::class, 'id_rkm', 'id');
    }
    

}
