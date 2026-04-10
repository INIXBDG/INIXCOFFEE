<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubChecklistKeperluan extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_keperluan_id',
        'materi_module',
        'materi_elearning',
        'cb_instruktur',
        'cb_peserta',
        'maksi_instruktur',
        'maksi_peserta',
        'kelas_ac',
        'kelas_jam',
        'kelas_buku',
        'kelas_pulpen',
        'kelas_permen',
        'kelas_camilan',
        'kelas_minuman',
        'kelas_lampu',
        'kelas_kondisi_kebersihan',
    ];

    public function checklistKeperluan()
    {
        return $this->belongsTo(ChecklistKeperluan::class, 'checklist_keperluan_id');
    }
}
