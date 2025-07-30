<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class izinTigaJam extends Model
{
    use HasFactory;

    protected $fillable = ['id_karyawan', 'jam_mulai', 'jam_selesai', 'alasan', 'durasi', 'alasan_approval', 'approval','tanggal_pengajuan'];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan', 'id');
    }
}
