<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelatihan extends Model
{
    use HasFactory;

    protected $table = 'pelatihans';

    protected $fillable = [
        'user_id',
        'nama_pelatihan',
        // 'penyedia',
        'vendor',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'harga',
        'status_approval',
        'approved_by',
        'approved_at',
        'id_pengajuan_barang',
        'bukti_pelatihan',
        'id_sertifikasi',
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

    public function sertifikasi()
    {
        return $this->belongsTo(Sertifikasi::class, 'id_sertifikasi');
    }
}
