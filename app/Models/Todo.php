<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_name',
        'category',
        'sort_order',
        'is_active'
    ];

    // Relasi ke transaksi checklist (jarang dipakai langsung, tapi ada baiknya didefinisikan)
    public function eventTodos()
    {
        return $this->hasMany(EventTodo::class, 'todo_id');
    }
}
