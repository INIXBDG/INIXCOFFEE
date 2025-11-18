<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailCatering extends Model
{
    use HasFactory;

    protected $fillable = ['id_catering', 'id_vendor', 'nama_makanan', 'jumlah', 'harga', 'keterangan'];

    public function vendor()
    {
        return $this->belongsTo(vendor::class, 'id_vendor', 'id');
    }
}
