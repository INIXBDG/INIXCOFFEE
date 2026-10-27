<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    public const KENDARAAN = 'Maintenance Kendaraan';
    public const KANTOR    = 'Maintenance Kantor';
    public const SECTIONS  = [self::KENDARAAN, self::KANTOR];

    protected $table = 'maintenances';

    protected $fillable = [
        'bulan',
        'jenis_maintenance',
        'tanggal',
        'biaya',
        'keterangan',
        'no_kk',
        'section',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'biaya'   => 'float',
        'bulan'   => 'integer',
    ];
}