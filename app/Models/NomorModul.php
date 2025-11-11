<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NomorModul extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor',
        'tipe',
        'status',
    ];

    public function moduls(): HasMany
    {
        return $this->hasMany(Modul::class, 'nomor', 'nomor');
    }
}