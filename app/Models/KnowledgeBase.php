<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class knowledgeBase extends Model
{
    use HasFactory;
    protected $fillable = [
        'divisi',
        'subdivisi',
        'title',
        'file_path',
        'file_type',
        'uploaded_by'
    ];
}