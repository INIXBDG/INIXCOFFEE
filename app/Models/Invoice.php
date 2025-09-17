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
        'id_rkm',
        'amount',
    ];

    /**
     * Get the RKM that owns the Invoice.
     */
    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm');
    }
}