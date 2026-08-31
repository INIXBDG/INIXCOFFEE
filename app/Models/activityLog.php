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

    protected static function booted()
    {
        static::saved(function ($activityLog) {
            if ($activityLog->user_id) {
                \Illuminate\Support\Facades\Cache::forget("activity_log_auth_{$activityLog->user_id}");
                \Illuminate\Support\Facades\Cache::forget("activity_log_visit_{$activityLog->user_id}");
                \Illuminate\Support\Facades\Cache::forget("activity_log_absen_{$activityLog->user_id}");
            }

            if (is_numeric($activityLog->status)) {
                $status = intval($activityLog->status);
                if ($status >= 100 && $status <= 199) {
                    \Illuminate\Support\Facades\Cache::forget("uptime_log_informasional");
                } elseif ($status >= 200 && $status <= 299) {
                    \Illuminate\Support\Facades\Cache::forget("uptime_log_success");
                } elseif ($status >= 300 && $status <= 399) {
                    \Illuminate\Support\Facades\Cache::forget("uptime_log_redirect");
                } elseif ($status >= 400 && $status <= 499) {
                    \Illuminate\Support\Facades\Cache::forget("uptime_log_client_error");
                } elseif ($status >= 500 && $status <= 599) {
                    \Illuminate\Support\Facades\Cache::forget("uptime_log_server_error");
                }
            }
        });
    }
}
