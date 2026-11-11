<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisFinanceInternalProcess extends Model
{
    protected $guarded = [];

    protected $fillable = ['tahun', 'bulan', 'pengajuan_barang', 'bukti_kas', 'catatan', 'analysis_target'];
}