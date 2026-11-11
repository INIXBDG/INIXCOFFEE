<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisFinanceOverride extends Model
{
    protected $table = 'analysis_finance_overrides';

    protected $fillable = ['tahun', 'periode', 'no', 'realisasi', 'pencapaian'];
}