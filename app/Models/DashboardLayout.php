<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class DashboardLayout extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'dashboard_layouts_global';

    protected $fillable = [
        'section_key',
        'sort_order',
        'card_order',
    ];

    protected $casts = [
        'card_order' => 'array',
    ];

    public static function cachedAll(): Collection
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(12), function () {
            return static::orderBy('sort_order', 'asc')->get();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
