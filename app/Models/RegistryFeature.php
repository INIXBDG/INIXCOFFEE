<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RegistryFeature extends Model
{
    use HasFactory;

    protected $table = 'registry_features';

    protected $fillable = [
        'tugas',
        'tipe',
        'fitur',
        'pemilik',
        'pengerja_id',
        'status',
        'tanggal_mulai',
        'tanggal_akhir',
        'catatan',
        'fakta',
        'harapan',
        'waktu_perkiraan',
        'ticket_id',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_akhir' => 'datetime',
    ];

    protected $appends = [
        'durasi_pengerjaan',
        'durasi_human',
    ];

    public function pengerja(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengerja_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Tickets::class, 'ticket_id');
    }

    public function getDurasiPengerjaanAttribute(): string
    {
        if (!$this->tanggal_mulai || !$this->tanggal_akhir) {
            return '0:00:00';
        }

        $totalSeconds = $this->tanggal_akhir->diffInSeconds($this->tanggal_mulai);

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function getDurasiHumanAttribute(): string
    {
        if (!$this->tanggal_mulai || !$this->tanggal_akhir) {
            return '0 Hari 0 Jam 0 Menit';
        }
        $totalMinutes = $this->tanggal_akhir->diffInMinutes($this->tanggal_mulai);

        $days = floor($totalMinutes / 1440);
        $remainingMinutes = $totalMinutes % 1440;
        $hours = floor($remainingMinutes / 60);
        $minutes = $remainingMinutes % 60;

        return "{$days} Hari {$hours} Jam {$minutes} Menit";
    }
}
