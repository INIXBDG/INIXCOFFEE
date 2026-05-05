<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama_lead',
        'perusahaan_id',
        'nama_pic',    // Penambahan atribut nama PIC
        'kontak_pic',
        'estimasi_nilai',
        'status',
        'sales_id',
    ];

    public function client()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id', 'id');
    }

    public function sales()
    {
        return $this->belongsTo(Karyawan::class, 'sales_id', 'kode_karyawan');
    }

    public function project()
    {
        return $this->hasOne(Project::class, 'lead_id', 'id');
    }
}
