<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisFinanceQuarterlyReport extends Model
{
    protected $guarded = [];

    protected $fillable = ['tahun', 'quarter', 'nilai_persen', 'skor_kinerja', 'status', 'catatan', 'keterangan'];
}