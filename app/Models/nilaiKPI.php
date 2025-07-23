<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class nilaiKPI extends Model
{
    use HasFactory;

    protected $fillable = ['id_evaluator', 'id_evaluated', 'kode_form', 'kode_kategori', 'name_variabel', 'pesan', 'nilai', 'status'];

    public function shareForms()
    {
        return shareForm::where('id_evaluator', $this->id_evaluator)
            ->where('id_evaluated', $this->id_evaluated)
            ->where('kode_form', $this->kode_form);
    }
}
