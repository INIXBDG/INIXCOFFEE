<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class souvenir extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_souvenir',
        'harga',
        'min_harga_pelatihan',
        'max_harga_pelatihan',
        'stok',
        'foto',
        'blob_foto',
    ];
    protected $hidden = ['blob_foto'];

    public function catatan()
    {
        return $this->hasMany(catatansouvenir::class, 'id_souvenir', 'id')->orderBy('created_at', 'desc');
    }
}
