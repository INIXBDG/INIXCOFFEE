<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KontrolTugas extends Model
{
    use HasFactory;

    protected $fillable = ['id_karyawan', 'id_DaftarTugas', 'status', 'Deadline_Date', 'bukti'];

    public function KategoriDaftarTugas()
    {
        return $this->hasOne(KategoriDaftarTugas::class, 'id', 'id_DaftarTugas');
    }

    public function karyawan()
    {
        return $this->hasOne(karyawan::class, 'id', 'id_karyawan');
    }
 
}
