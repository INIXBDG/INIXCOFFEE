<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'chart_id',
        'jabatan',
        'divisi',
        'parent_id',
        'sort_order',
        'karyawan_ids',
        'additional_parents',
    ];

    protected $casts = [
        'karyawan_ids' => 'array',
        'additional_parents' => 'array',
    ];

    public function chart()
    {
        return $this->belongsTo(OrgChart::class, 'chart_id');
    }

    public function parent()
    {
        return $this->belongsTo(OrgStructure::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(OrgStructure::class, 'parent_id')->orderBy('sort_order');
    }
}