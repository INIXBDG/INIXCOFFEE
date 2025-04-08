<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisTunjangan extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_tunjangan',
        'nilai',
        'tipe',
        'hitung',
        'divisi',
    ];
    public function tunjangankaryawan()
    {
        return $this->hasMany(TunjanganKaryawan::class);
    }
}
