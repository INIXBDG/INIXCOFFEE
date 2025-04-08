<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class changeexam extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_exam',
        'keterangan',
        'status',
        'kode_karyawan',
    ];

    public function exam()
    {
        return $this->belongsTo(eksam::class, 'id_exam', 'id');
    }
}
