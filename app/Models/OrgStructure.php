<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgStructure extends Model
{
    use HasFactory;

    protected $fillable = ['jabatan', 'divisi', 'parent_id', 'sort_order', 'karyawan_ids', 'additional_parents'];
    protected $casts = [
        'karyawan_ids' => 'array',
        'additional_parents' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(OrgStructure::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(OrgStructure::class, 'parent_id')->orderBy('sort_order');
    }

    public function karyawans()
    {
        return $this->hasMany(Karyawan::class, 'jabatan', 'jabatan')
            ->where(function ($query) {
                $query->whereIn('jabatan', ['Direktur', 'Direktur Utama'])->where('status_aktif', '1');
            })
            ->orWhere(function ($query) {
                $query->where('status_aktif', '1')->where('jabatan', '!=', 'Outsource')->where('jabatan', '!=', 'Pilih Jabatan')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNotNull('nip');
            });
    }
}
