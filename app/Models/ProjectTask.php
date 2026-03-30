<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'assignee_id',
        'status',
        'task_file',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(Karyawan::class, 'assignee_id', 'kode_karyawan');
    }
}