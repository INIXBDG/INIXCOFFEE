<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpjsChangeLog extends Model
{
    protected $table = 'bpjs_change_logs';

    protected $fillable = [
        'perhitungan_id',
        'karyawan_id',
        'bulan',
        'tahun',
        'field_name',
        'old_value',
        'new_value',
        'description',
        'changed_by',
    ];

    public function perhitungan()
    {
        return $this->belongsTo(PerhitunganTunjanganHR::class, 'perhitungan_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'karyawan_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}