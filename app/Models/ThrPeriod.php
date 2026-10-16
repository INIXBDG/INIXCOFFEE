<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThrPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_periode',
        'tahun',
        'tanggal_jatuh_tempo',
        'min_masa_kerja_bulan',
        'full_masa_kerja_bulan',
        'status',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date:Y-m-d',
        'min_masa_kerja_bulan' => 'integer',
        'full_masa_kerja_bulan' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(ThrItem::class, 'thr_period_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
