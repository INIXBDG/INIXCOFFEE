<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_perusahaan',
        'sales_key',
        'nama',
        'status',
        'email',
        'cp',
        'divisi',
    ];

    public function peluangs()
    {
        return $this->hasMany(Peluang::class, 'id_contact', 'id');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }
}
