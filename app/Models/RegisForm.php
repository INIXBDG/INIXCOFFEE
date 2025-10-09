<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisForm extends Model
{
    use HasFactory;
    protected $fillable = ['id_peluang', 'path', 'name'];

    public function peluang()
    {
        return $this->belongsTo(Peluang::class, 'id_peluang', 'id');
    }
}
