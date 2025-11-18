<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingCatering extends Model
{
    use HasFactory;

    protected $fillable = ['id_catering', 'tracking', 'tanggal', 'keterangan'];
}
