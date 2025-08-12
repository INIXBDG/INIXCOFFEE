<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class nilaiKPI extends Model
{
    use HasFactory;

    protected $fillable = ['id_evaluator', 'id_evaluated', 'kode_form', 'kode_kategori', 'name_variabel', 'jenis_penilaian', 'pesan', 'nilai', 'status'];

    public function shareForms()
    {
        return shareForm::where('id_evaluator', $this->id_evaluator)
            ->where('id_evaluated', $this->id_evaluated)
            ->where('kode_form', $this->kode_form);
    }

    // Di Model nilaiKPI
    public function form()
    {
        return $this->belongsTo(formPenilaian::class, 'kode_form', 'kode_form');
    }

    public function share()
    {
        return $this->belongsTo(shareForm::class, 'kode_form', 'kode_form');
    }
}
