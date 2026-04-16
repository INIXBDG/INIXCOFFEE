<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisAnnualReport extends Model
{
    use HasFactory;

    protected $table = 'analysis_annual_reports';

    protected $fillable = ['year', 'description', 'file_paths'];

    protected $casts = [
        'file_paths' => 'array',
    ];
}
