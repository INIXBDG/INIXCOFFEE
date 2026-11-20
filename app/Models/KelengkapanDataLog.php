<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KelengkapanDataLog extends Model
{
    protected $table = 'kelengkapan_data_logs';

    protected $fillable = ['sumber', 'id_rkm', 'perubahan', 'user_id'];

    public static function catatTeks(string $sumber, $idRkm, string $teks): void
    {
        try {
            static::create([
                'sumber'    => $sumber,
                'id_rkm'    => (int) $idRkm,
                'perubahan' => $teks,
                'user_id'   => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[KelengkapanDataLog] gagal mencatat', ['error' => $e->getMessage()]);
        }
    }

    public static function dariDirty(string $sumber, $idRkm, Model $model): void
    {
        $abaikan = ['created_at', 'updated_at'];
        $baris = [];

        foreach ($model->getDirty() as $kolom => $baru) {
            if (in_array($kolom, $abaikan, true)) {
                continue;
            }

            if ($model->exists) {
                $lama = $model->getOriginal($kolom);
                $kosongLama = $lama === null || $lama === '';
                $kosongBaru = $baru === null || $baru === '' || (is_numeric($baru) && (float) $baru === 0.0);
                if ($kosongLama && $kosongBaru) {
                    continue;
                }
                $baris[] = $kolom . ': ' . self::tampil($lama) . ' -> ' . self::tampil($baru);
            } else {
                $baris[] = $kolom . ': ' . self::tampil($baru);
            }
        }

        if (empty($baris)) {
            return;
        }

        static::catatTeks($sumber, $idRkm, ($model->exists ? 'Diubah' : 'Dibuat') . ' | ' . implode('; ', $baris));
    }

    private static function tampil($v): string
    {
        if ($v === null || $v === '') {
            return '-';
        }
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d');
        }
        return (string) $v;
    }
}