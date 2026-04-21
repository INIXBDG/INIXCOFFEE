<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $table = 'modules';

    protected $fillable = [
        'title',
        'category',
        'link',
        'description',
        'kode_karyawan',
    ];

    public function instructors()
    {
        return $this->belongsToMany(User::class, 'module_instructors', 'module_id', 'user_id');
    }

    public function klaimModul()
    {
        return $this->hasOne(PengajuanKlaimModul::class);
    }

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'kode_karyawan', 'kode_karyawan');
    }
}