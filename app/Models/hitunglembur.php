<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class hitunglembur extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_lembur',
        'nilai_lembur',
        'approval_gm',
        'alasan',
    ];
    protected $guarded = [];

    public function lembur()
    {
        return $this->belongsTo(lembur::class, 'id_lembur', 'id');
    }
}
