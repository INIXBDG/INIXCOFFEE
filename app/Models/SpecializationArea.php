<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecializationArea extends Model
{
    use HasFactory;
    protected $table = 'specialization_areas';

    protected $fillable = [
        'specialization',
        'kode_instruktur',
        'detail_specialization',
    ];
}
