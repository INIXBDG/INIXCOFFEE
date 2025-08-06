<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shareForm extends Model
{
    use HasFactory;

    protected $fillable = ['id_evaluator', 'id_evaluated', 'divisi_evaluator', 'kode_form', 'jenis_penilaian'];

    public function evaluator()
    {
        return $this->belongsTo(karyawan::class, 'id_evaluator');
    }

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan');
    }

    public function formPenilaian()
    {
        return $this->hasOne(formPenilaian::class, 'kode_form', 'kode_form');
    }
}
