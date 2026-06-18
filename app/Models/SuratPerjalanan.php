<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPerjalanan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'surat_perjalanans';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'id_karyawan',
        'tipe',
        'tujuan',
        'tanggal_berangkat',
        'tanggal_pulang',
        'alasan',
        'ratemakan',
        'ratespj',
        'ratetaksi',
        'total',
        'durasi',
        'approval_manager',
        'approval_hrd',
        'approval_gm',
        'approval_direksi',
        'jenis_dinas',
        'jadwal_RKM',
        'approval_finance',
        'bukti_transfer',
    ];

    /**
     * Tipe data untuk atribut yang didefinisikan.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_berangkat' => 'datetime',
        'tanggal_pulang' => 'datetime',
        'ratemakan' => 'decimal:2',
        'ratespj' => 'decimal:2',
        'ratetaksi' => 'decimal:2',
        'total' => 'decimal:2',
        'approval_manager' => 'string',
        'approval_hrd' => 'string',
        'approval_direksi' => 'string',
        'approval_gm' => 'string',
        'approval_finance' => 'string',
    ];

    public function jurnalAkuntansi()
    {
        return $this->hasOne(JurnalAkuntansi::class, 'id_surat_perjalanan', 'id');
    }
    
    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan', 'id');
    }

    public function RKM()
    {
        return $this->belongsTo(RKM::class, 'jadwal_RKM', 'id');
    }
}
