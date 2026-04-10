<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatatanMeetingSales extends Model
{
    use HasFactory;

    protected $fillable = [
        'laporan_id',
        'sales_id',
        'catatan'
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanHarianSales::class, 'laporan_id');
    }
    public function sales()
    {
        return $this->belongsTo(karyawan::class, 'sales_id');
    }
}
