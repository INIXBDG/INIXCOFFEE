<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisFinanceSetting extends Model
{
    protected $guarded = [];

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