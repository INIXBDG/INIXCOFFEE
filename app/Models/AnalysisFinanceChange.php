<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisFinanceChange extends Model
{
    protected $table = 'analysis_finance_changes';

    protected $fillable = ['tahun', 'quarter', 'no', 'keterangan', 'nilai_lalu', 'nilai_ini'];
}