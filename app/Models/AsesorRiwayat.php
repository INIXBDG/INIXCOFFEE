<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsesorRiwayat extends Model
{
    protected $table = 'asesor_riwayat';
    protected $guarded = [];
    protected $casts = ['tanggal' => 'date'];

    public function asesor() { return $this->belongsTo(LspAsesor::class, 'asesor_id'); }

    // Nama yang ditampilkan: dari asesor jika terelasi, kalau tidak pakai nama manual
    public function getNamaAttribute(): ?string
    {
        return $this->asesor?->nama_lengkap ?? $this->nama_manual;
    }

    public function scopeJenis($q, ?string $jenis)
    {
        return $jenis ? $q->where('jenis', $jenis) : $q;
    }
}
