<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PelamarFolder extends Model
{
    protected $table = 'pelamar_folders';

    protected $fillable = ['folder_id', 'pelamar_id', 'rating', 'catatan', 'file_penilaian', 'dinilai_oleh', 'tanggal_dinilai', 'is_archived'];

    protected $attributes = [
        'is_archived' => false,
    ];

    protected $casts = [
        'tanggal_dinilai' => 'datetime',
    ];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function pelamar()
    {
        return $this->belongsTo(Pelamar::class);
    }

    public function interviewer()
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }
}
