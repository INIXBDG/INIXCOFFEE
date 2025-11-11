<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modul extends Model
{
    protected $table = 'moduls';

    protected $fillable = [
        'nomor', 
        'tipe',
        'kode_materi',
        'nama_materi',
        'awal_training',
        'akhir_training',
        'nama_peserta',
        'kontak_peserta',
        'jumlah',
        'harga_satuan',
        'subtotal',
        'grand_total',
        'note',
    ];

    protected $casts = [
        'awal_training' => 'date',
        'akhir_training' => 'date',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($modul) {
            $modul->subtotal = $modul->jumlah * $modul->harga_satuan;
            $modul->grand_total = $modul->subtotal;
        });
    }

    public function nomorModul(): BelongsTo
    {
        return $this->belongsTo(NomorModul::class, 'nomor', 'nomor');
    }
}