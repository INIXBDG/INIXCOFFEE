<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisFinanceSetting extends Model
{
    protected $fillable = [
        'tahun',
        'target_tagihan',
        'target_pencairan',
        'target_ide',
        'bobot_tagihan',
        'bobot_pencairan',
        'bobot_ide',
    ];

    public static function forYear(int $tahun): self
    {
        return static::firstOrNew(['tahun' => $tahun], [
            'target_tagihan'   => 95,
            'target_pencairan' => 100,
            'target_ide'       => 2,
            'bobot_tagihan'    => 40,
            'bobot_pencairan'  => 35,
            'bobot_ide'        => 25,
        ]);
    }
}