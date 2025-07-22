<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tickets extends Model
{
    use HasFactory;
    protected $fillable = [
        'no_user',
        'deskripsi',
        'status',
        'id_ts',
        'alasan',
    ];
}
