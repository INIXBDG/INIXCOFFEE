<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'tanggal_invoice',
        'due_date',
        'purchase_order',
        'id_rkm',
        'amount',
        'catatan_pembayaran',
        'bank_name',
        'account_number',
        'file_path'
    ];

    /**
     * Get the RKM that owns the Invoice.
     */
    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm');
    }
        // relasi ke Kuitansi
// app/Models/Invoice.php
public function kwitansi()
{
    return $this->hasOne(Kwitansi::class);
}

}