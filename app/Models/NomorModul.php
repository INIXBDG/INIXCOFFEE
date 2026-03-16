<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomorModul extends Model
{
    use HasFactory;
    protected $fillable = [
        'no_modul',
        'type',
        'status',
        'note_modul',
        'note_peserta',
        'uploaded'
    ];

    public function moduls()
    {
        return $this->hasMany(Modul::class, 'no_modul', 'id');
    }
}
