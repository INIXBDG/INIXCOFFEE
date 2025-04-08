<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Notifications\Notifiable;

class karyawan extends Model
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'foto',
        'nip',
        'nama_lengkap',
        'divisi',
        'jabatan',
        'rekening_maybank',
        'rekening_bca',
        'status_aktif',
        'awal_probation',
        'akhir_probation',
        'awal_kontrak',
        'akhir_kontrak',
        'awal_tetap',
        'akhir_tetap',
        'keterangan',
        'kode_karyawan',
        'ttd',
        'cuti',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'karyawan_id');
    }

    public function perusahaan()
    {
        return $this->hasOne(Perusahaan::class, 'karyawan_key', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'karyawan_key', 'id');
    }

    public function rkmsSales()
    {
        return $this->hasMany(Rkm::class, 'sales_key', 'kode_karyawan');
    }

    public function rkmsInstruktur()
    {
        return $this->hasMany(Rkm::class, 'instruktur_key', 'kode_karyawan');
    }

    public function rkmsInstruktur2()
    {
        return $this->hasMany(Rkm::class, 'instruktur_key2', 'kode_karyawan');
    }

    public function rkmsAsisten()
    {
        return $this->hasMany(Rkm::class, 'asisten_key', 'kode_karyawan');
    }
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($foto) => url('/storage/posts/' . $foto),
        );
    }

    public function tunjangankaryawan()
    {
        return $this->hasMany(TunjanganKaryawan::class);
    }

    public function lembur()
    {
        return $this->hasMany(lembur::class);
    }

}
