<?php

namespace App\Models;

use App\Support\CacheRegistry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Materi extends Model
{
    use HasFactory;
    protected $table = 'materis';
    protected $fillable = [
        'nama_materi',
        'kode_materi',
        'kategori_materi',
        'vendor',
        'durasi',
        'status',
        'keterangan',
        'tipe_materi',
        'silabus',
        'alias',
        'kode_alias',
        'alias_exam',
        'kategori_exam',
    ];

    public function rkms()
    {
        return $this->hasMany(RKM::class, 'materi_key', 'id');
    }

    // Add the relationship to Peluang
    public function peluangs()
    {
        return $this->hasMany(Peluang::class, 'materi', 'id');
    }

    public function labs()
    {
        return $this->belongsToMany(Lab::class, 'lab_materi', 'materi_id', 'lab_id');
    }

    public function subscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'subscription_materi', 'materi_id', 'subscription_id');
    }

    public static function cachedAll(): Collection
    {
        return Cache::remember(CacheRegistry::materiKey('all'), now()->addHours(12), function () {
            return static::all();
        });
    }

    protected static function booted(): void
    {
        $bump = static fn () => CacheRegistry::bumpMateriVersion();

        static::saved($bump);
        static::deleted($bump);
    }
}
