<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiPDF extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_rkm',
        'pdf_path',
        'pdf_name',
    ];
}
