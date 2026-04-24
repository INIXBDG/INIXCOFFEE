<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisReport extends Model
{
    use HasFactory;

    protected $table = 'analysis_reports';

    protected $fillable = [
        'user_id',
        'description',
        'file_paths',
        'year',
        'month',
        'nilai'
    ];

    /**
     * Konversi tipe data kolom
     */
    protected $casts = [
        'file_paths' => 'array',
    ];

    /**
     * Relasi ke model User (Pembuat Laporan)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
