<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'state',
        'date_start',
        'date_end',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyActivities()
    {
        return $this->hasMany(DailyActivity::class, 'id_task'); 
    }
}
