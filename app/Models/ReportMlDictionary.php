<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportMlDictionary extends Model
{
    protected $table = 'report_ml_dictionaries';

    protected $fillable = [
        'context_keyword',
        'field_type',
        'field_value',
        'hit_count',
        'last_used_at',
        'created_by',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];
}