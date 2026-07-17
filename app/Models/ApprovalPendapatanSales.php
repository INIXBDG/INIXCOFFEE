<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalPendapatanSales extends Model
{
    use HasFactory;

    protected $table = 'approval_pendapatan_sales';

    protected $fillable = [
        'id_rkm',
        'no_faktur',
        'no_invoice',
        'harga_net',
        'pax',
        'diskon',
        'entertainment',
        'total_diskon',
        'total_pa',
        'total_cashback',
        'total_uang_saku',
        'total_akomodasi',
        'jenis_transport',
        'biaya_transport',
        'oleh_oleh',
        'total_penjualan_sales',
        'status',
        'materi',
        'perusahaan',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected $casts = [
        'id_rkm' => 'integer',
        'harga_net' => 'integer',
        'pax' => 'integer',
        'diskon' => 'integer',
        'total_diskon' => 'integer',
        'total_pa' => 'integer',
        'total_cashback' => 'integer',
        'total_uang_saku' => 'integer',
        'total_akomodasi' => 'integer',
        'biaya_transport' => 'integer',
        'oleh_oleh' => 'integer',
        'total_penjualan_sales' => 'integer',
        'materi' => 'integer',
        'perusahaan' => 'integer',
        'tanggal_mulai' => 'date:Y-m-d',
        'tanggal_selesai' => 'date:Y-m-d',
    ];

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm');
    }

    public function dataMateri()
    {
        return $this->belongsTo(Materi::class, 'materi');
    }

    public function dataPerusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan');
    }

    public function pendapatan()
    {
        return $this->hasOne(ApprovalPendapatan::class, 'id_rkm', 'id_rkm');
    }
}
