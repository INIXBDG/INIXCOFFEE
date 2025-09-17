<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peluang extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_contact',
        'id_sales',
        'id_rkm',
        'materi',
        'catatan',
        'harga',
        'netsales',
        'periode_mulai',
        'periode_selesai',
        'pax',
        'final',
        'biru',
        'merah',
        'tahap',
        'tentatif',
    ];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_contact', 'id');
    }

    public function aktivitas()
    {
        return $this->hasMany(Aktivitas::class, 'id_peluang', 'id');
    }

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }

    // Add the relationship to Materi
    public function materiRelation()
    {
        return $this->belongsTo(Materi::class, 'materi', 'id');
    }

    public function regis()
    {
        return $this->belongsTo(RegisForm::class, 'id_peluang');
    }

    // public static function updateNetSalesFromRkm(int $id_rkm): void
    // {
    //     // Ambil semua data perhitunganNetSales untuk id_rkm ini
    //     $data = perhitunganNetSales::where('id_rkm', $id_rkm)->get();

    //     // Jika tidak ada data, set netSales = 0
    //     if ($data->isEmpty()) {
    //         self::where('id_rkm', $id_rkm)->update(['netSales' => 0]);
    //         return;
    //     }

    //     // Hitung total harga dan total dikurangi
    //     $totalHarga = $data->sum('harga_penawaran');
    //     $totalDikurangi = $data->sum('transportasi') +
    //                       $data->sum('penginapan') +
    //                       $data->sum('fresh_money') +
    //                       $data->sum('cashback') +
    //                       $data->sum('diskon') +
    //                       $data->sum('entertaint') +
    //                       $data->sum('souvenir');

    //     $netSales = $totalHarga - $totalDikurangi;

    //     // Update nilai netSales di tabel peluang
    //     self::where('id_rkm', $id_rkm)->update(['netSales' => $netSales]);
    // }

}
