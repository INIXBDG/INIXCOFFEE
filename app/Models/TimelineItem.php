<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimelineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'year_mapping_id',
        'item_date',
        'content',
        'color'
    ];

    protected $casts = [
        'item_date' => 'date', // Otomatis jadi objek Carbon
    ];

    // Relasi balik ke Mapping (Untuk mengetahui item ini milik event bulan apa)
    public function yearMapping()
    {
        return $this->belongsTo(YearMapping::class, 'year_mapping_id');
    }
}
