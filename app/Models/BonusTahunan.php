<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusTahunan extends Model
{
    use HasFactory;

    protected $table = 'bonus_tahunans';

    protected $fillable = [
        'karyawan_id',
        'tahun',
        'bulan',
        'basic_salary',
        'masa_kerja_bulan',
        'masa_kerja_teks',
        'skema_bonus',
        'multiplier',
        'nominal_bonus',
        'catatan',
        'is_locked',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'multiplier' => 'decimal:2',
        'nominal_bonus' => 'decimal:2',
        'masa_kerja_bulan' => 'integer',
        'is_locked' => 'boolean',
        'tanggal_bergabung' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'karyawan_id');
    }
}
