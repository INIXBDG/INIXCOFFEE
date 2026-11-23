<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaWebinarSales extends Model
{
    use HasFactory;

    protected $fillable = [
        'year_mapping_id',
        'email_peserta',
        'nama_peserta',
        'no_hp',
        'id_perusahaan',
        'id_sales',
    ];

    public function yearMapping()
    {
        return $this->belongsTo(YearMapping::class, 'year_mapping_id', 'id');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan', 'id');
    }

    public function sales()
    {
        return $this->belongsTo(karyawan::class, 'id_sales', 'kode_karyawan');
    }
}