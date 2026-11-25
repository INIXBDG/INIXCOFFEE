<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lsp extends Model
{
    protected $fillable = [
        'nama',
        'jenis',
        'bidang',
        'based',
        'nomor_pks_mou',
        'tanggal_mulai',
        'tanggal_berakhir',
        'pic',
        'no_hp',
        'email',
        'website',
        'status',
        'karyawan',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai'    => 'date',
        'tanggal_berakhir' => 'date',
    ];

    public function programs()     { return $this->hasMany(LspLpkProgram::class, 'lsp_id'); }
    public function sertifikasis() { return $this->programs()->where('jenis', 'sertifikasi'); }
    public function pelatihans()   { return $this->programs()->where('jenis', 'pelatihan'); }

    public function asesors()
    {
        return $this->belongsToMany(LspAsesor::class, 'asesor_lsp', 'lsp_id', 'asesor_id')->withTimestamps();
    }
}
