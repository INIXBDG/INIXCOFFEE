<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TunjanganKaryawan extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id_karyawan',
        'bulan',
        'tahun',
        'jenis_tunjangan',
        'keterangan',
        'jumlah_absensi',
        'total',
        'status_approval',
        'approved_by',
        'approved_at',
        'rejection_note'
    ];

    protected $casts = [
        'approved_at' => 'datetime'
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan');
    }

    public function jenistunjangan()
    {
        return $this->belongsTo(jenistunjangan::class, 'jenis_tunjangan');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scope untuk filter berdasarkan status
    public function scopePending($query)
    {
        return $query->where('status_approval', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status_approval', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status_approval', 'rejected');
    }

    // Scope untuk filter berdasarkan bulan dan tahun
    public function scopeByPeriod($query, $bulan, $tahun)
    {
        return $query->where('bulan', $bulan)->where('tahun', $tahun);
    }
}