<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisFinanceInitiative extends Model
{
    protected $guarded = [];

    protected $fillable = ['tahun', 'no', 'initiative', 'description', 'keterangan', 'analysis_target', 'estimated_impact', 'is_implemented'];

    protected $casts = ['is_implemented' => 'boolean'];
}