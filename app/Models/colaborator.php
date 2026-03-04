<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class colaborator extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_partner', 
        'title',
        'type',
        'start_date', 
        'end_date',
        'status',
        'desc',
        'document_mou',
    ];

    public function perusahaan()
    {
        // Menghubungkan kolom nama_perusahaan di dbkliens ke kolom nama_perusahaan di perusahaans
        return $this->belongsTo(Perusahaan::class, 'nama_partner', 'nama_perusahaan');
    }
}
