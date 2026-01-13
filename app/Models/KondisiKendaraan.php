<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KondisiKendaraan extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'jenis_kendaraan',

        // Kondisi Fisik
        'fisik_baik',
        'bersih',
        'wiper_baik',
        'klakson_baik',
        'lampu_baik',
        'tekanan_ban_baik',
        'ban_baik',
        'ban_cadangan_lengkap',
        'setir_pedal_baik',
        'catatan_kondisi',

        // Mesin
        'oli_baik',
        'radiator_baik',
        'air_wiper_baik',
        'minyak_rem_baik',
        'aki_baik',
        'catatan_mesin',

        // Dokumen & Perlengkapan
        'dokumen_lengkap',
        'jas_hujan_ada',
        'pengharum_ada',
        'catatan_perlengkapan',

        // Fasilitas
        'ac_baik',
        'audio_baik',
        'charger_ada',
        'air_minum_ada',
        'tisu_ada',
        'hand_sanitizer_ada',
        'catatan_fasilitas',

        // BBM & Tol
        'bbm_cukup',
        'etol_aktif',
        'tanggal_pemeriksaan'
    ];

    protected $casts = [
        'fisik_baik' => 'boolean',
        'bersih' => 'boolean',
        'wiper_baik' => 'boolean',
        'klakson_baik' => 'boolean',
        'lampu_baik' => 'boolean',
        'tekanan_ban_baik' => 'boolean',
        'ban_baik' => 'boolean',
        'ban_cadangan_lengkap' => 'boolean',
        'setir_pedal_baik' => 'boolean',
        'oli_baik' => 'boolean',
        'radiator_baik' => 'boolean',
        'air_wiper_baik' => 'boolean',
        'minyak_rem_baik' => 'boolean',
        'aki_baik' => 'boolean',
        'dokumen_lengkap' => 'boolean',
        'jas_hujan_ada' => 'boolean',
        'pengharum_ada' => 'boolean',
        'ac_baik' => 'boolean',
        'audio_baik' => 'boolean',
        'charger_ada' => 'boolean',
        'air_minum_ada' => 'boolean',
        'tisu_ada' => 'boolean',
        'hand_sanitizer_ada' => 'boolean',
        'bbm_cukup' => 'boolean',
        'etol_aktif' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
