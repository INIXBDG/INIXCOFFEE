<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTargetKPI extends Model
{
    use HasFactory;

    protected $fillable = ['id_targetKPI', 'jabatan', 'divisi', 'id_data_target', 'detail_jangka', 'manual_value', 'manual_document'];

    public function targetKPI()
    {
        return $this->belongsTo(targetKPI::class, 'id_targetKPI', 'id');
    }

    public function detailPersonKPI()
    {
        return $this->hasMany(detailPersonKPI::class, 'detailTargetKey', 'id');
    }

    public function dataTarget()
    {
        return $this->belongsTo(DataTarget::class, 'id_data_target', 'id');
    }

    public function getTipeTargetAttribute()
    {
        return $this->dataTarget?->tipe_target;
    }

    public function getNilaiTargetAttribute()
    {
        return $this->dataTarget?->nilai_target;
    }

    public function getJangkaTargetAttribute()
    {
        return $this->dataTarget?->jangka_target;
    }
}
