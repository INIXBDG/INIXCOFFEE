<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTodo extends Model
{
    use HasFactory;

    protected $fillable = [
        'year_mapping_id',
        'todo_id',
        'is_checked',
        'pic',
        'notes'
    ];

    protected $casts = [
        'is_checked' => 'boolean', // Memastikan output JSON jadi true/false
    ];

    // Relasi ke Mapping (Event Triwulan)
    public function yearMapping()
    {
        return $this->belongsTo(YearMapping::class, 'year_mapping_id');
    }

    // Relasi ke Master Todo (Untuk mengambil nama tugas)
    public function todo()
    {
        return $this->belongsTo(Todo::class, 'todo_id');
    }
}
