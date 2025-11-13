<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RegistryFeature extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'registry_features';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
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
    ];

    /**
     * Tipe data (casts) untuk atribut.
     * Mengubah kolom tanggal menjadi objek Carbon secara otomatis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_akhir' => 'datetime',
    ];

    /**
     * Menambahkan atribut virtual (accessor) ke dalam representasi JSON/array.
     *
     * @var array
     */
    protected $appends = [
        'durasi_pengerjaan',
        'durasi_human',
    ];

        public function pengerja(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengerja_id');
    }

    /**
     * Accessor untuk mendapatkan "Durasi Pengerjaan"
     * (Format: HH:MM:SS, cth: "184:00:00")
     */
    public function getDurasiPengerjaanAttribute(): string
    {
        if (!$this->tanggal_mulai || !$this->tanggal_akhir) {
            return '0:00:00';
        }

        $totalSeconds = $this->tanggal_akhir->diffInSeconds($this->tanggal_mulai);

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        // sprintf untuk format H:MM:SS
        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Accessor untuk mendapatkan "Durasi (Dalam Hari)"
     * (Format: X Hari Y Jam Z Menit)
     */
    public function getDurasiHumanAttribute(): string
    {
        if (!$this->tanggal_mulai || !$this->tanggal_akhir) {
            return '0 Hari 0 Jam 0 Menit';
        }

        // Menggunakan diff for humans dari Carbon
        // return $this->tanggal_mulai->diffForHumans($this->tanggal_akhir, true);

        // Atau format manual yang lebih presisi seperti di gambar
        $totalMinutes = $this->tanggal_akhir->diffInMinutes($this->tanggal_mulai);

        $days = floor($totalMinutes / 1440); // 1440 = 24 * 60
        $remainingMinutes = $totalMinutes % 1440;
        $hours = floor($remainingMinutes / 60);
        $minutes = $remainingMinutes % 60;

        return "{$days} Hari {$hours} Jam {$minutes} Menit";
    }
}
