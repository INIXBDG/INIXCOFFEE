<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LspLpkProgram extends Model
{
    protected $table = 'lsp_lpk_programs';

    protected $fillable = [
        'lsp_id',
        'jenis',
        'nama_skema',
        'jenis_skema',
        'jenjang_kkni',
        'pelaksanaan',
        'harga_sertifikasi_per_pax',
        'harga_pelatihan_per_hari',
        'keterangan',
    ];

    public function lsp() { return $this->belongsTo(Lsp::class, 'lsp_id'); }

    public function scopeSertifikasi($q) { return $q->where('jenis', 'sertifikasi'); }
    public function scopePelatihan($q)   { return $q->where('jenis', 'pelatihan'); }
}
