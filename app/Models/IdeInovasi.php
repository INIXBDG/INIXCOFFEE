<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdeInovasi extends Model
{
    use HasFactory;

    protected $table = 'ide_inovasis';

    protected $fillable = [
        'id_karyawan',
        'judul',
        'deskripsi',
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan');
    }
}
