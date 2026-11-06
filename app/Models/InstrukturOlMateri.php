<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstrukturOlMateri extends Model
{
    use SoftDeletes;

    protected $table = 'instruktur_ol_materis';

    protected $fillable = [
        'instruktur_ol_id',
        'rkm_id',
        'judul',
        'keterangan',
        'file_path',
        'file_nama_asli',
    ];

    protected $appends = ['file_url'];

    public function instruktur()
    {
        return $this->belongsTo(InstrukturOl::class, 'instruktur_ol_id');
    }

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'rkm_id');
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path
            ? asset(InstrukturOl::UPLOAD_DIR . '/materi/' . $this->file_path)
            : null;
    }
}