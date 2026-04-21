<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectHandover extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'bast_file',
        'final_report_file',
        'handover_date',
        'status',
        'notes',
    ];

    /**
     * Relasi ke entitas Project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
}