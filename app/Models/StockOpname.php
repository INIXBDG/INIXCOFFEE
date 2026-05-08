<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $fillable = ['kode_barang', 'nama_barang', 'kategori', 'satuan', 'stock_awal', 'stock_sekarang', 'notes', 'pic'];

    public function logs()
    {
        return $this->hasMany(StockOpnameLog::class);
    }

    public function picData()
    {
        return $this->belongsTo(karyawan::class, 'pic', 'kode_karyawan');
    }
}
