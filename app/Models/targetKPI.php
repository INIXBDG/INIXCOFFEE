<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class targetKPI extends Model
{
    use HasFactory;

    protected $fillable = ['id_pembuat', 'id_data_target', 'judul', 'deskripsi', 'status'];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_pembuat', 'id');
    }

    public function detailTargetKPI()
    {
        return $this->hasMany(DetailTargetKPI::class, 'id_targetKPI', 'id');
    }

    public function dataTarget()
    {
        return $this->belongsTo(DataTarget::class, 'id_data_target', 'id');
    }

    public function detailPersonKPI()
    {
        return $this->hasMany(detailPersonKPI::class, 'id_target', 'id');
    }
}