<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisYearDescription extends Model
{
    use HasFactory;

    protected $table = 'analysis_year_descriptions';

    protected $fillable = [
        'year',
        'description',
        'note',
    ];
}
