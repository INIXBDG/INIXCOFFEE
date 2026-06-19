<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobDesk extends Model
{
    use HasFactory;

    protected $table = 'job_desks';

    protected $fillable = [
        'id_org',
        'fungsi_utama',
        'tujuan_jabatan',
        'kualifikasi_pendidikan',
        'pengalaman_kerja',
        'kompetensi',
        'karakteristik_pribadi',
        'tugas_tanggung_jawab',
        'wewenang',
        'sop',
    ];

    protected $casts = [
        'kompetensi'           => 'array',
        'tugas_tanggung_jawab' => 'array',
        'wewenang'             => 'array',
        'sop'                  => 'array',
    ];

    public function orgStructure()
    {
        return $this->belongsTo(OrgStructure::class, 'id_org');
    }
}