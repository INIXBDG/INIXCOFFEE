<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataTarget extends Model
{
    use HasFactory;

    protected $fillable = ['asistant_route', 'jangka_target', 'tipe_target', 'nilai_target'];
}
