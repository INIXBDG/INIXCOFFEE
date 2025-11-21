<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPengajuanSouvenir extends Model
{
    use HasFactory;

    protected $table = 'detail_pengajuan_souvenirs';

    public $timestamps = false;

    protected $fillable = [
        'id_pengajuan_souvenir',
        'id_souvenir',
        'pax',
        'harga_satuan',
        'harga_total',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'harga_total' => 'decimal:2',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanSouvenir::class, 'id_pengajuan_souvenir', 'id');
    }

    public function souvenir()
    {
        return $this->belongsTo(souvenir::class, 'id_souvenir', 'id');
    }
}
