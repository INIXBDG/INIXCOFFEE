<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PelamarPenilaian extends Model
{
    protected $fillable = [
        'pelamar_id', 'folder_id', 'dinilai_oleh', 'tahap_rekrutmen', 'tahap_interview',
        'rating', 'catatan', 'file_penilaian', 'tanggal_dinilai',
    ];

    protected $casts = [
        'rating' => 'integer',
        'tanggal_dinilai' => 'datetime',
    ];

    public function pelamar()
    {
        return $this->belongsTo(Pelamar::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function penilai()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'dinilai_oleh');
    }

    public function ringkas(): array
    {
        $nama = $this->penilai?->nama_lengkap ?? $this->penilai?->name ?? $this->penilai?->username ?? 'Tidak diketahui';
        $jabatan = $this->penilai?->jabatan ?? '-';
        $ext = strtolower(pathinfo((string) $this->file_penilaian, PATHINFO_EXTENSION));

        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'catatan' => $this->catatan,
            'tahap' => $this->tahap_rekrutmen,
            'tahap_label' => Pelamar::TAHAP[$this->tahap_rekrutmen] ?? 'Tidak tercatat',
            'tahap_interview_label' => $this->tahap_interview
                ? (Pelamar::TAHAP_INTERVIEW[$this->tahap_interview] ?? $this->tahap_interview)
                : null,
            'folder_nama' => $this->folder?->nama ?? '-',
            'penilai_nama' => $nama,
            'penilai_jabatan' => $jabatan,
            // nama lama, supaya tab Penilaian di detail pelamar tetap jalan
            'interviewer_nama' => $nama,
            'interviewer_jabatan' => $jabatan,
            'tanggal_dinilai' => $this->tanggal_dinilai?->format('d M Y H:i'),
            'file_penilaian' => $this->file_penilaian,
            'file_url' => $this->file_penilaian ? Storage::url($this->file_penilaian) : null,
            'file_ext' => $ext ?: null,
            'file_tipe' => in_array($ext, ['pdf', 'docx', 'doc'], true) ? $ext : null,
        ];
    }
}