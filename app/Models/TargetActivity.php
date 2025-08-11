<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetActivity extends Model
{
    use HasFactory;
    protected $fillable = [
        'Contact',
        'Call',
        'Visit',
        'Email',
    ];
}
