<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pph21Laporan extends Model
{
    use HasFactory;

    protected $table = 'pph21_laporans';

    protected $fillable = [
        'bulan',
        'tahun',
        'lapor_kortex_at',
        'lapor_kortex_by',
        'kode_billing_nomor',
        'kode_billing_file',
        'kode_billing_at',
        'kode_billing_by',
        'bukti_bayar_file',
        'bukti_bayar_at',
        'bukti_bayar_by',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'lapor_kortex_at' => 'datetime',
        'kode_billing_at' => 'datetime',
        'bukti_bayar_at' => 'datetime',
    ];

    public function kortexBy()
    {
        return $this->belongsTo(User::class, 'lapor_kortex_by');
    }

    public function kodeBillingBy()
    {
        return $this->belongsTo(User::class, 'kode_billing_by');
    }

    public function buktiBayarBy()
    {
        return $this->belongsTo(User::class, 'bukti_bayar_by');
    }
}