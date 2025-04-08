<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class approvalexam extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_exam',
        'spv_sales',
        'sales',
        'technical_support',
        'office_manager',
        'status',
        'ttd_sales',
        'ttd_ts',
        'ttd_off',
    ];

    public function exam()
    {
        return $this->belongsTo(eksam::class, 'id_exam', 'id');
    }
}
