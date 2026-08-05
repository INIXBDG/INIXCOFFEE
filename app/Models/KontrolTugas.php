<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KontrolTugas extends Model
{
    use HasFactory;

    protected $fillable = ['id_karyawan', 'id_DaftarTugas', 'status', 'Deadline_Date', 'bukti','urutan'];

    public function KategoriDaftarTugas()
    {
        return $this->belongsTo(KategoriDaftarTugas::class, 'id_DaftarTugas', 'id');
    }

    public function karyawan()
    {
        return $this->hasOne(karyawan::class, 'id', 'id_karyawan');
    }
 
}
