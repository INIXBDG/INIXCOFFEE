<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObTools extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_alat',
        'kategori',
        'qty',
    ];

    public function kondisiTools()
    {
        return $this->hasMany(KondisiTools::class, 'id_alat', 'id');
    }
}
