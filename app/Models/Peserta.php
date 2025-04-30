<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama',
        'jenis_kelamin',
        'email',
        'no_hp',
        'alamat',
        'perusahaan_key',
        'tanggal_lahir',
        'checkregist',
    ];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_key', 'id');
    }

    public function latestRegistrasi()
    {
        return $this->hasOne(Registrasi::class, 'id_peserta', 'id')->latestOfMany('created_at');
    }
    

}
