<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PicPenagihanInvoice extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang diasosiasikan dengan model.
     *
     * @var string
     */
    protected $table = 'pic_penagihan_invoices';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_rkm',
        'perusahaan_id',
        'alamat',
        'category',
        'pic',
        'telepon',
        'status',
    ];

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id')->withTrashed();
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id', 'id');
    }

    public function outstanding()
    {
        return $this->hasOne(outstanding::class, 'id_rkm', 'id_rkm');
    }

}
