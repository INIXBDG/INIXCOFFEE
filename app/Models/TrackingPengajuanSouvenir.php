<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingPengajuanSouvenir extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'tracking_pengajuan_souvenirs';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'id_pengajuan_souvenir',
        'tracking',
        'tanggal',
    ];

    /**
     * Tipe data untuk atribut.
     *
     * @var array
     */
    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Relasi ke data pengajuan utamanya.
     */
    public function pengajuanSouvenir()
    {
        return $this->belongsTo(PengajuanSouvenir::class, 'id_pengajuan_souvenir', 'id');
    }
}
