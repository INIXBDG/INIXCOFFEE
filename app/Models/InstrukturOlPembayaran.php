<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstrukturOlPembayaran extends Model
{
    use SoftDeletes;

    protected $table = 'instruktur_ol_pembayarans';

    protected $fillable = [
        'instruktur_ol_id',
        'rkm_id',
        'judul',
        'harga',
        'kali_pax',
        'pax',
        'total',
        'tanggal_bayar',
        'keterangan',
        'bukti_path',
        'bukti_nama_asli',
    ];

    protected $casts = [
        'kali_pax'      => 'boolean',
        'harga'         => 'integer',
        'pax'           => 'integer',
        'total'         => 'integer',
        'tanggal_bayar' => 'date',
    ];

    protected $appends = ['bukti_url'];

    public function instruktur()
    {
        return $this->belongsTo(InstrukturOl::class, 'instruktur_ol_id');
    }

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'rkm_id');
    }

    public function getBuktiUrlAttribute(): ?string
    {
        return $this->bukti_path
            ? asset(InstrukturOl::UPLOAD_DIR . '/bukti-pembayaran/' . $this->bukti_path)
            : null;
    }
}