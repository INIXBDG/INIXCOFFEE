<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class catatansouvenir extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_souvenir',
        'catatan',
        'stok_perubahan',
        'stok_terakhir',
        'stok_terbaru',
    ];

    public function souvenir()
    {
        return $this->belongsTo(souvenir::class, 'id_souvenir', 'id');
    }
}
