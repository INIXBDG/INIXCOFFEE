<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityInstruktur extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity',
        'status',
        'desc',
        'doc',
        'activity_date',
        'on_progress_at',
        'failed_at',
        'completed_at',
        'is_locked',
        'id_rkm',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'on_progress_at' => 'datetime',
        'failed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm');
    }
}
