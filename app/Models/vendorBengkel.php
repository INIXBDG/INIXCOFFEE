<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vendorBengkel extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama',
        'is_active',
        'foto',
        'keterangan',
        'no_hp',
        'no_rekening',
        'alamat'
    ];
}
