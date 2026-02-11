<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class targetKPI extends Model
{
    use HasFactory;

    protected $fillable = ['id_targetKPI', 'id_pembuat', 'asistant_route', 'judul', 'deskripsi', 'status'];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_pembuat', 'id');
    }

    public function detailTargetKPI()
    {
        return $this->hasMany(DetailTargetKPI::class, 'id_targetKPI', 'id');
    }

    public function detailPersonKPI()
    {
        return $this->hasMany(DetailPersonKPI::class, 'id_target', 'id');
    }
}
