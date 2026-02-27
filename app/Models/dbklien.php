<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dbklien extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'jenis_kelamin',
        'email',
        'no_hp',
        'alamat',
        'nama_perusahaan',
        'tanggal_lahir',
        'nama_materi',
        'sales_key',
        'created_at',
    ];
public function perusahaan()
{
    // Menghubungkan kolom nama_perusahaan di dbkliens ke kolom nama_perusahaan di perusahaans
    return $this->belongsTo(Perusahaan::class, 'nama_perusahaan', 'nama_perusahaan');
}
}
