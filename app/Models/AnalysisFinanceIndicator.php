<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisFinanceIndicator extends Model
{
    protected $table = 'analysis_finance_indicators';

    protected $fillable = ['tahun', 'no', 'analysis', 'deskripsi', 'formula'];
}