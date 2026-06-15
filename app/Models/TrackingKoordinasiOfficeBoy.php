<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingKoordinasiOfficeBoy extends Model
{
    use HasFactory;

    protected $fillable = [
        'koordinasi_id',
        'status',
        'updated_by',
    ];

    public function koordinasi()
    {
        return $this->belongsTo(KoordinasiOfficeBoy::class, 'koordinasi_id', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'updated_by', 'id');
    }
}
