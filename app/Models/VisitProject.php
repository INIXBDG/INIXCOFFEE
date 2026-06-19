<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitProject extends Model
{
    use HasFactory;

    protected $table = 'visit_projects';

    protected $fillable = [
        'kegiatan',
        'lokasi',
        'pic_name',
        'tanggal',
        'photo_path',
        'desc',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}
