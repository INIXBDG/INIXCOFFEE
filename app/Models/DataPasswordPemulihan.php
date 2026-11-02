<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPasswordPemulihan extends Model
{
    public const GAGAL = 'gagal';
    public const TERVERIFIKASI = 'terverifikasi';
    public const SELESAI = 'selesai';
    public const KEDALUWARSA = 'kedaluwarsa';

    protected $table = 'data_password_pemulihan';

    protected $fillable = [
        'user_id', 'user_nama', 'user_jabatan', 'status',
        'jawaban_nama', 'jawaban_judul', 'alasan', 'konfirmasi', 'hasil',
        'ip_address', 'device', 'diverifikasi_at', 'selesai_at',
    ];

    protected $casts = [
        'konfirmasi' => 'boolean',
        'hasil' => 'array',
        'diverifikasi_at' => 'datetime',
        'selesai_at' => 'datetime',
    ];
}