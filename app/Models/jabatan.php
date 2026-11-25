<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class jabatan extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'master:jabatan:all';

    protected $fillable = [
        'nama_jabatan',
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
}
