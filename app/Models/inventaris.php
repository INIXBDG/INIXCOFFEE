<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class inventaris extends Model
{
    use HasFactory;

    protected $fillable = [
        'idbarang',
        'name',
        'merk_kode_seri_hardware',
        'qty',
        'kodebarang',
        'type',
        'harga_beli',
        'waktu_pembelian',
        'pengguna',
        'ruangan',
        'kondisi',
        'deskripsi',
        'total_harga',
        'satuan'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Ensure waktu_pembelian and kodebarang are set
            if (empty($model->waktu_pembelian) || empty($model->kodebarang)) {
                throw new \Exception('waktu_pembelian and kodebarang must be set to generate idbarang');
            }

            // Extract year from waktu_pembelian (2 digits, e.g., '24' for 2024)
            $year = Carbon::parse($model->waktu_pembelian)->format('y');

            // Count existing items with the same kodebarang and purchase year
            $count = static::where('kodebarang', $model->kodebarang)
                ->whereYear('waktu_pembelian', Carbon::parse($model->waktu_pembelian)->year)
                ->count() + 1;

            // Format idbarang: INX/Year/Kode_barang/Seri_barang
            $model->idbarang = sprintf('INX/%s/%s/%d', $year, $model->kodebarang, $count);
        });
    }

    public function periodic_checks()
    {
        return $this->hasMany(checkbarang::class, 'idbarang', 'idbarang');
    }

    public function services()
    {
        return $this->hasMany(service::class, 'idbarang', 'idbarang');
    }
}