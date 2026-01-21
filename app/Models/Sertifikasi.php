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
        'penyedia',
        'tanggal_ujian',
        'tanggal_berlaku_dari',
        'tanggal_berlaku_sampai',
        'harga',
        'vendor',
        'status_approval',
        'approved_by',
        'approved_at',
    ];

    /**
     * Mengambil data pemilik sertifikat
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mengambil data manager yang melakukan approval
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}