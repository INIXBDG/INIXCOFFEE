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
        'email',
        'foto_npwp',
        'history_sales',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'sales_key', 'kode_karyawan');
    }

    public function rkms()
    {
        return $this->hasMany(RKM::class, 'perusahaan_key', 'id');
    }

    public function peserta()
    {
        return $this->hasMany(Peserta::class, 'perusahaan_key', 'id');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'id_perusahaan', 'id');
    }

    public function peluang()
    {
        return $this->hasMany(Peluang::class, 'id_contact', 'id');
    }

    public function scopeForSales($query, $salesKey)
    {
        return $query->where('sales_key', $salesKey);
    }

    public function getHistorySalesArrayAttribute()
    {
        return json_decode($this->history_sales ?? '[]', true);
    }

    public function canBeTransferred()
    {
        return !empty($this->sales_key) && $this->status !== 'nonaktif';
    }
}