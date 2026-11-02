<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportMlDictionary extends Model
{
    protected $table = 'report_ml_dictionaries';

    protected $guarded = [];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];
}