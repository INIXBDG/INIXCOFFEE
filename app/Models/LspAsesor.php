<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LspAsesor extends Model
{
    protected $table = 'lsp_asesors';
    protected $guarded = [];

    public function lsps()
    {
        return $this->belongsToMany(Lsp::class, 'asesor_lsp', 'asesor_id', 'lsp_id')->withTimestamps();
    }

    public function riwayat()
    {
        return $this->hasMany(AsesorRiwayat::class, 'asesor_id');
    }

    public function getTotalPembayaranAttribute(): float
    {
        return (float) $this->riwayat()->where('jenis', 'pembayaran')->sum('harga');
    }
}
