<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LspLpkProgram extends Model
{
    protected $table = 'lsp_lpk_programs';
    protected $guarded = [];

    public function lsp() { return $this->belongsTo(Lsp::class, 'lsp_id'); }

    public function scopeSertifikasi($q) { return $q->where('jenis', 'sertifikasi'); }
    public function scopePelatihan($q)   { return $q->where('jenis', 'pelatihan'); }
}
