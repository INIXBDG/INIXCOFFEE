<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BundlingExam extends Model
{
    use HasFactory;

    protected $fillable = ['id_rkm', 'id_exam', 'bundling', 'keterangan'];
}
