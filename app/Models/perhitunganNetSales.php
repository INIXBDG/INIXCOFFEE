<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class perhitunganNetSales extends Model
{
    use HasFactory;

    protected $fillable = ['id_rkm', 'transportasi', 'jenis_transportasi', 'akomodasi_peserta', 'akomodasi_tim', 'keterangan_akomodasi_tim', 'fresh_money', 'entertaint', 'keterangan_entertaint', 'souvenir', 'cashback',  'sewa_laptop', 'tgl_pa', 'tipe_pembayaran', 'deskripsi_tambahan', 'id_tracking', 'deleted_at', 'deleted_by'];
    protected $casts = [
        'transportasi' => 'integer',
        'akomodasi_peserta' => 'integer',
        'akomodasi_tim' => 'integer',
        'fresh_money' => 'integer',
        'entertaint' => 'integer',
        'souvenir' => 'integer',
        'cashback' => 'integer',
        'sewa_laptop' => 'integer',
    ];

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }

    public function approvedNetSales()
    {
        return $this->hasMany(approvedNetSales::class, 'id_rkm', 'id_rkm');
    }
    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan'); // sesuaikan 'id_karyawan' dengan nama kolom foreign key yang benar
    }

    public function trackingNetSales()
    {
        return $this->belongsTo(trackingNetSales::class, 'id_tracking', 'id');
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'id_peserta');
    }

    public function jurnalAkuntansi()
    {
        return $this->hasOne(JurnalAkuntansi::class, 'id_perhitungan_net_sales', 'id');
    }
}
