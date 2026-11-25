<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Helper terpusat untuk key cache terstruktur & versioned.
 * Dirancang agar aman dan optimal pada Redis maupun driver file/array
 * tanpa pernah memerlukan Cache::flush() (FLUSHDB).
 */
class CacheRegistry
{
    public const OUTSTANDING_VERSION_KEY = 'ver:outstanding';
    public const RKM_VERSION_KEY = 'ver:rkm';
    public const MATERI_VERSION_KEY = 'ver:materi';

    public static function version(string $versionKey): int
    {
        $ver = Cache::get($versionKey);
        if (!is_numeric($ver) || (int) $ver < 1) {
            Cache::forever($versionKey, 1);
            return 1;
        }

        return (int) $ver;
    }

    public static function bumpVersion(string $versionKey): int
    {
        $next = self::version($versionKey) + 1;
        Cache::forever($versionKey, $next);

        return $next;
    }

    public static function outstandingKey(string $suffix): string
    {
        return sprintf('outstanding:v%d:%s', self::version(self::OUTSTANDING_VERSION_KEY), $suffix);
    }

    public static function bumpOutstandingVersion(): int
    {
        return self::bumpVersion(self::OUTSTANDING_VERSION_KEY);
    }

    public static function rkmKey(string $suffix): string
    {
        return sprintf('rkm:v%d:%s', self::version(self::RKM_VERSION_KEY), $suffix);
    }

    public static function bumpRkmVersion(): int
    {
        return self::bumpVersion(self::RKM_VERSION_KEY);
    }

    public static function rememberRkm(string $suffix, int $ttlSeconds, callable $callback)
    {
        return Cache::remember(self::rkmKey($suffix), now()->addSeconds($ttlSeconds), $callback);
    }

    public static function materiKey(string $suffix): string
    {
        return sprintf('materi:v%d:%s', self::version(self::MATERI_VERSION_KEY), $suffix);
    }

    public static function bumpMateriVersion(): int
    {
        return self::bumpVersion(self::MATERI_VERSION_KEY);
    }

    public static function unreadNotifCountKey(int|string $userId): string
    {
        return "notif:unread_count:{$userId}";
    }

    public static function rememberUnreadNotifCount(int|string $userId, callable $resolver, int $ttlSeconds = 60): int
    {
        return (int) Cache::remember(
            self::unreadNotifCountKey($userId),
            now()->addSeconds($ttlSeconds),
            $resolver
        );
    }

    public static function forgetUnreadNotifCount(int|string $userId): void
    {
        Cache::forget(self::unreadNotifCountKey($userId));
    }
}
