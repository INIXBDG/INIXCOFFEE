<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class notif extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_user',
        'tipe_notifikasi',
        'isi_notifikasi',
        'tanggal_awal',
        'tanggal_akhir',
    ];
    public function users()
    {
        return $this->belongsTo(user::class, 'id_user', 'username');
    }
}
