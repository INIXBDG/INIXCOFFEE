<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalPendapatan extends Model
{
    use HasFactory;

    protected $table = 'approval_pendapatans';

    protected $fillable = ['id_rkm', 'no_faktur', 'no_invoice', 'harga_net', 'pax', 'diskon', 'entertainment', 'total_diskon', 'total_pa', 'total_cashback', 'total_uang_saku', 'total_akomodasi', 'jenis_akomodasi', 'jenis_transport', 'biaya_transport', 'oleh_oleh', 'total_penjualan_sales', 'PPN', 'PPH', 'status', 'materi', 'tanggal_mulai', 'tanggal_selesai', 'perusahaan', 'jumlah_pembayaran', 'tanggal_pembayaran', 'biaya_admin', 'total_penjualan_kotor', 'exam', 'total_penjualan_bersih'];

    public function dataMateri()
    {
        return $this->belongsTo(Materi::class, 'materi', 'id');
    }

    public function dataPerusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan', 'id');
    }

    public function rkm()
    {
        return $this->belongsTo(Rkm::class, 'id_rkm');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'id_rkm', 'id_rkm');
    }
}
