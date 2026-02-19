<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTargetKPI extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_targetKPI',
        'jabatan',
        'divisi',
        'jangka_target',
        'detail_jangka',
        'tipe_target',
        'nilai_target',
        'manual_value',
        'manual_document'
    ];

    public function targetKPI()
    {
        return $this->belongsTo(targetKPI::class, 'id_targetKPI', 'id');
    }

    public function detailPersonKPI()
    {
        return $this->hasMany(detailPersonKPI::class, 'detailTargetKey', 'id');
    }
}
