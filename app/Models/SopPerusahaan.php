<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SopPerusahaan extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_perusahaan',
        'sop',
        'judul',
    ];

    protected $casts = [
        'sop' => 'array',
    ];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }
}
