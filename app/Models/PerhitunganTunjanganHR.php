<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerhitunganTunjanganHR extends Model
{
    use SoftDeletes;

    protected $table = 'perhitungan_tunjangan_h_r_s';

    protected $fillable = [
        'karyawan_id', 'bulan', 'tahun',
        'gaji_pokok', 'salary_bpjstk', 'umk_bandung',
        'tunjangan_detail', 'total_tunjangan',
        'jht_perusahaan', 'jkm_perusahaan', 'jkk_perusahaan', 'jp_perusahaan', 'total_bpjstk_perusahaan',
        'jht_karyawan', 'jp_karyawan', 'total_bpjstk_karyawan',
        'bpjs_kes_perusahaan', 'bpjs_kes_karyawan',
        'total_bpjs_perusahaan', 'total_bpjs_karyawan',
        'potongan_pph21', 'potongan_kasbon', 'potongan_denda', 'potongan_lain', 'total_potongan_lain',
        'thp_kotor', 'thp_bersih', 'total_biaya_perusahaan',
        'status', 'catatan',
        'created_by', 'updated_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'tunjangan_detail' => 'array',
        'approved_at' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'karyawan_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(karyawan::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['draft', 'calculated']);
    }
}