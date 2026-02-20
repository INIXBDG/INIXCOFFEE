<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryNetSales extends Model
{
    use HasFactory;
    protected $fillable = ['id_user', 'id_rkm','data'];

    protected $casts = [
        'data' => 'array',
    ];

    public function User()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
