<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomplainPeserta extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function nilaifeedback()
    {
        return $this->belongsTo(Nilaifeedback::class, 'nilaifeedback_id');
    }
}
