<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuarterEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'year_mapping_id',
        'title',
        'speaker_name',
        'description'
    ];

    // Relasi balik ke Mapping
    public function mapping()
    {
        return $this->belongsTo(YearMapping::class, 'year_mapping_id');
    }
}
