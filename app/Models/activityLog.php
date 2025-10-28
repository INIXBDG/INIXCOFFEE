<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class activityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'url',
        'ip',
        'user_agent',
        'platform',
        'browser',
        'device',
        'method',
        'detail',
        'is_up',
        'response_time_ms',
        'checked_at'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'user_id', 'id');
    }
}
