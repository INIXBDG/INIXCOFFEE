<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class checklistRKM extends Model
{
    use HasFactory;

    protected $fillable = ['id_rkm', 'registrasi_form', 'surat_kontrak', 'PA', 'PO'];

    public function rkm()
    {
        return $this->belongsTo(Rkm::class, 'id_rkm');
    }
}
