<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class JenisTunjangan extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'master:jenis_tunjangan:all';

    protected $fillable = [
        'nama_tunjangan',
        'nilai',
        'tipe',
        'hitung',
        'divisi',
    ];

    public static function cachedAll(): Collection
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(12), function () {
            return static::all();
        });
    }

    protected static function booted(): void
    {
        $clear = static fn () => Cache::forget(self::CACHE_KEY);

        static::saved($clear);
        static::deleted($clear);
    }

    public function tunjangankaryawan()
    {
        return $this->hasMany(TunjanganKaryawan::class);
    }
}
