<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerbaikanKendaraan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_kondisi_kendaraan',
        'kendaraan',
        'id_user',
        'type_condition',
        'type_vehicle_condition',
        'type_repair',
        'deskripsi_kondisi',
        'tanggal_kejadian',
        'waktu_kejadian',
        'lokasi',
        'estimasi',
        'status',
        'bukti',
        'tanggal_perbaikan',
        'selesai_perbaikan',
        'detail_perbaikan',
        'document'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}