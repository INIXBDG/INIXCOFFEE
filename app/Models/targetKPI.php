<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class targetKPI extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_pembuat',
        'assistant_route',
        'judul',
        'deksripsi',
        'jabatan',
        'divisi',
        'jangka_target',
        'detail_jangka',
        'tipe_target',
        'nilai_target',
        'status',
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_pembuat', 'id');
    }
}
