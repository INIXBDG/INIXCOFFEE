<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetActivity extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_sales',
        'Contact',
        'Call',
        'Visit',
        'Email',
        'Meet',
        'DB',
        'PA',
        'PI',
        'Incharge',
        'Telemarketing',
        'FormM',
        'FormK',
        'deadline',
    ];

    public function aktivitas()
    {
        return $this->hasMany(Aktivitas::class, 'id_sales', 'id_sales');
    }


}
