<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobProfile extends Model
{
    use HasFactory;

    protected $fillable = ['karyawan_id', 'qualifications', 'descriptions', 'compensation_benefit'];

    protected $casts = [
        'qualifications' => 'array',
        'descriptions' => 'array',
        'compensation_benefit' => 'array',
    ];
}
