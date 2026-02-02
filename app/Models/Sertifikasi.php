<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sertifikasi extends Model
{
    use HasFactory;

    protected $table = 'sertifikasis';

    protected $fillable = [
        'user_id',
        'nama_sertifikat',
        // 'penyedia',
        'tanggal_ujian',
        'tanggal_berlaku_dari',
        'tanggal_berlaku_sampai',
        'harga',
        'vendor',
        'status_approval',
        'keterangan',
        'approved_by',
        'approved_at',
        'id_pengajuan_barang',
        'bukti_sertifikasi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function pengajuan_barang()
    {
        return $this->belongsTo(PengajuanBarang::class, 'id_pengajuan_barang');
    }

    public function pelatihan()
    {
        return $this->hasOne(Pelatihan::class, 'id_sertifikasi');
    }
}
