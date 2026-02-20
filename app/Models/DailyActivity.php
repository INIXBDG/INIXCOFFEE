<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'id_task',
        'activity',
        'status',
        'description',
        'doc',
        'start_date',
        'end_date',
        'on_progress_at',
        'on_progress_next_day_at',
        'failed_at',
        'completed_at',
    ];  

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'on_progress_at' => 'datetime',
        'on_progress_next_day_at' => 'datetime',
        'failed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Update status dan isi waktu sesuai status
     */
    public function updateStatus($newStatus)
    {
        $this->status = $newStatus;

        switch ($newStatus) {
            case 'On Progres':
                $this->on_progress_at = now();
                break;

            case 'On Progres Dilanjutkan Besok':
                $this->on_progress_next_day_at = now();
                break;

            case 'Gagal':
                $this->failed_at = now();
                break;

            case 'Selesai':
                $this->completed_at = now();
                break;
        }

        $this->save();
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'id_task');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
