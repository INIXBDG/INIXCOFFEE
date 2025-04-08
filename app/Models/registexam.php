<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class registexam extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_exam',
        'id_peserta',
        'email',
        'email_exam',
        'akun_exam',
        'tanggal_exam',
        'kode_exam',
        'pukul',
        'nama_perguruan_tinggi',
        'alamat_perguruan_tinggi',
        'jurusan',
        'tahun_lulus',
        'invoice',
        'cc',
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'id_peserta', 'id');
    }

    public function exam()
    {
        return $this->belongsTo(Eksam::class, 'id_exam', 'id');
    }

    public function creditcard()
    {
        return $this->hasOne(cc::class, 'id', 'cc'); // sesuaikan key-nya

    }
}
