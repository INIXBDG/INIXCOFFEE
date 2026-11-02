<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstrukturOl extends Model
{
    use SoftDeletes;

    public const UPLOAD_DIR = 'uploads/instruktur-ol';

    protected $table = 'instruktur_ols';

    protected $fillable = [
        'kode_karyawan',
        'nama',
        'core',
        'no_hp',
        'keterangan',
        'cv_path',
        'ktp_path',
    ];

    protected $appends = ['cv_url', 'ktp_url'];

    public function pembayarans()
    {
        return $this->hasMany(InstrukturOlPembayaran::class, 'instruktur_ol_id');
    }

    public function silabus()
    {
        return $this->hasMany(InstrukturOlSilabus::class, 'instruktur_ol_id');
    }

    public function getCvUrlAttribute(): ?string
    {
        return $this->cv_path ? asset(self::UPLOAD_DIR . '/cv/' . $this->cv_path) : null;
    }

    public function getKtpUrlAttribute(): ?string
    {
        return $this->ktp_path ? asset(self::UPLOAD_DIR . '/ktp/' . $this->ktp_path) : null;
    }
}