<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KoordinasiOfficeBoy extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_tugas',
        'status',
        'karyawan',
        'deadline',
        'catatan',
        'created_by',
    ];

    public function tracking() 
    {
        return $this->hasMany(TrackingKoordinasiOfficeBoy::class, 'koordinasi_id', 'id');
    }

    public function pembuat()
    {
        return $this->belongsTo(karyawan::class, 'created_by', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'karyawan', 'id');
    }
}
