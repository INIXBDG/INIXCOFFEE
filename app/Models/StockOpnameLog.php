<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameLog extends Model
{
    protected $fillable = ['barang_id', 'tanggal', 'stock_sebelumnya', 'stock_hari_ini', 'selisih', 'notes', 'updated_by'];

    public function barang()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'updated_by', 'kode_karyawan');
    }
}
