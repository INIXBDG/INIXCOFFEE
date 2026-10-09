<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kwitansi extends Model
{
    protected $fillable = [
        'invoice_id',
        'id_rkm',
        'tanggal_cetak',
        'dicetak_oleh',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
        public function rkm()
    {
        return $this->hasOneThrough(RKM::class, Invoice::class, 'id', 'id', 'invoice_id', 'rkm_id');
    }
    public function karyawan()
{
    return $this->belongsTo(karyawan::class, 'karyawan_id'); 
    // pastikan kolom 'karyawan_id' ada di tabel kwitansi
}

}
