<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThrItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'thr_period_id',
        'karyawan_id',
        'nama_lengkap',
        'jabatan',
        'status_karyawan',
        'rekening_maybank',
        'tanggal_join',
        'masa_kerja_tahun',
        'masa_kerja_bulan',
        'masa_kerja_hari',
        'total_masa_kerja_bulan',
        'masa_kerja_teks',
        'basic_salary',
        'nilai_thr_sistem',
        'nilai_thr_final',
        'is_manual',
        'catatan',
    ];

    protected $casts = [
        'tanggal_join' => 'date:Y-m-d',
        'masa_kerja_tahun' => 'integer',
        'masa_kerja_bulan' => 'integer',
        'masa_kerja_hari' => 'integer',
        'total_masa_kerja_bulan' => 'float',
        'basic_salary' => 'float',
        'nilai_thr_sistem' => 'float',
        'nilai_thr_final' => 'float',
        'is_manual' => 'boolean',
    ];

    protected $appends = ['status_ketentuan'];

    public function getStatusKetentuanAttribute()
    {
        $full = $this->period ? (int)$this->period->full_masa_kerja_bulan : 12;
        if ($this->total_masa_kerja_bulan >= $full) {
            return 'full';
        } elseif ($this->total_masa_kerja_bulan > 0) {
            return 'prorata';
        }
        return 'ineligible';
    }

    public function period()
    {
        return $this->belongsTo(ThrPeriod::class, 'thr_period_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'karyawan_id');
    }
}
