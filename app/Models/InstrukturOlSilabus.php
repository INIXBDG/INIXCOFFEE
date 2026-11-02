<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstrukturOlSilabus extends Model
{
    use SoftDeletes;

    protected $table = 'instruktur_ol_silabus';

    protected $fillable = [
        'instruktur_ol_id',
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

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path
            ? asset(InstrukturOl::UPLOAD_DIR . '/silabus/' . $this->file_path)
            : null;
    }
}