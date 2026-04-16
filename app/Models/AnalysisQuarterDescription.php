<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisQuarterDescription extends Model
{
    use HasFactory;

    protected $table = 'analysis_quarter_descriptions';

    protected $fillable = [
        'year',
        'quarter',
        'description',
        'file_paths', // Penambahan kolom file
    ];

    protected $casts = [
        'file_paths' => 'array', // Konversi otomatis ke array
    ];
}
