<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pph21Karyawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id',
        'ptkp',
        'status_menikah',
        'anak',
    ];

    protected $casts = [
        'anak' => 'array',
        'status_menikah' => 'boolean',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}