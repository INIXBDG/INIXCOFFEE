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

    public function allRegistrasi()
    {
        return $this->hasMany(Registrasi::class, 'id_peserta', 'id');
    }

    public static function formatNama($nama)
    {
        if (!$nama) return $nama;

        $words = explode(' ', $nama);

        $result = [];

        foreach ($words as $word) {

            // Jika ada titik atau sudah uppercase semua → anggap gelar
            if (str_contains($word, '.') || strtoupper($word) === $word) {

                // Tapi kalau uppercase biasa (JOHN) tetap diubah
                if (!str_contains($word, '.')) {
                    $result[] = ucfirst(strtolower($word));
                } else {
                    $result[] = strtoupper($word);
                }

            } else {
                $result[] = ucfirst(strtolower($word));
            }
        }

        return implode(' ', $result);
    }

    


}
