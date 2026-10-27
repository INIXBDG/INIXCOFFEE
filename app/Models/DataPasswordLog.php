<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPasswordLog extends Model
{
    public $timestamps = false;

    protected $table = 'data_password_logs';

    protected $fillable = [
        'user_id', 'user_nama', 'user_jabatan', 'aksi', 'data_id', 'data_judul',
        'ip_address', 'device', 'user_agent', 'keterangan', 'sumber', 'waktu',
    ];

    protected $casts = ['waktu' => 'datetime'];
}