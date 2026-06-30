<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KondisiTools extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_alat',
        'kondisi',
        'tanggal_pemeriksaan',
        'catatan',
    ];

    public function alat()
    {
        return $this->belongsTo(ObTools::class, 'id_alat', 'id');
    }
}
