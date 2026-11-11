<?php

namespace App\Support;

use App\Models\DataPassword;

class LegalitasVault
{
    public const IDLE_MENIT = 5;
    private const KEY = 'legalitas_unlocked_until';

    public static function master(): ?DataPassword
    {
        return DataPassword::where('kategori', DataPassword::MASTER_LEGALITAS)->first();
    }

    public static function isHrd(): bool
    {
        return auth()->check() && auth()->user()->jabatan === 'HRD';
    }

    public static function unlocked(): bool
    {
        $until = session(self::KEY);

        return $until && now()->timestamp < $until;
    }

    public static function touch(): void
    {
        session([self::KEY => now()->addMinutes(self::IDLE_MENIT)->timestamp]);
    }

    public static function forget(): void
    {
        session()->forget(self::KEY);
    }
}