<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PelamarRiwayat extends Model
{
    use HasFactory;

    protected $table = 'pelamar_riwayats';

    protected $fillable = [
        'pelamar_id',
        'tahap_dari',       
        'tahap_ke',       
        'aksi',             
        'keterangan',       
        'rating',
        'oleh',            
        'user_id',         
        'metadata',       
    ];

    protected $casts = [
        'metadata' => 'array',
        'rating'   => 'integer',
    ];

    public function pelamar()
    {
        return $this->belongsTo(Pelamar::class, 'pelamar_id');
    }

    public function getTahapDariLabelAttribute(): string
    {
        return Pelamar::TAHAP[$this->tahap_dari] ?? ucfirst($this->tahap_dari ?? '-');
    }

    public function getTahapKeLabelAttribute(): string
    {
        return Pelamar::TAHAP[$this->tahap_ke] ?? ucfirst($this->tahap_ke ?? '-');
    }

    public static function catat(int $pelamarId, string $aksi, array $data = []): self
    {
        return self::create([
            'pelamar_id'  => $pelamarId,
            'tahap_dari'  => $data['tahap_dari']  ?? null,
            'tahap_ke'    => $data['tahap_ke']    ?? null,
            'aksi'        => $aksi,
            'keterangan'  => $data['keterangan']  ?? null,
            'rating'      => $data['rating']      ?? null,
            'oleh'        => $data['oleh']        ?? auth()->user()->name ?? 'System',
            'user_id'     => $data['user_id']     ?? auth()->id(),
            'metadata'    => $data['metadata']    ?? null,
        ]);
    }
}