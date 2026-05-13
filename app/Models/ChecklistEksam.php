<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistEksam extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_exam',
        'status'
    ];

    public function exam() {
        return $this->belongsTo(eksam::class);
    }
}
