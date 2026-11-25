<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class souvenir extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const CACHE_KEY = 'master:souvenir:all';

    protected $fillable = [
        'nama_souvenir',
        'harga',
        'min_harga_pelatihan',
        'max_harga_pelatihan',
        'stok',
        'foto',
        'blob_foto',
        'deleted_by',
    ];
    protected $hidden = ['blob_foto'];

    public function catatan()
    {
        return $this->hasMany(catatansouvenir::class, 'id_souvenir', 'id')->orderBy('created_at', 'desc');
    }

    public function pengajuanSouvenir()
    {
        return $this->hasMany(PengajuanSouvenir::class, 'id_souvenir', 'id');
    }

    public function penambahanSouvenir()
    {
        return $this->hasMany(PenambahanSouvenir::class, 'id_souvenir', 'id');
    }

    /**
     * Ambil daftar souvenir tanpa kolom binary `blob_foto` agar hemat I/O MySQL & RAM Redis.
     */
    public static function cachedAll(): Collection
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(12), function () {
            return static::select([
                'id',
                'nama_souvenir',
                'harga',
                'min_harga_pelatihan',
                'max_harga_pelatihan',
                'stok',
                'foto',
                'created_at',
                'updated_at',
            ])->get();
        });
    }

    protected static function booted(): void
    {
        $clear = static fn () => Cache::forget(self::CACHE_KEY);

        static::saved($clear);
        static::deleted($clear);
        static::restored($clear);
    }
}
