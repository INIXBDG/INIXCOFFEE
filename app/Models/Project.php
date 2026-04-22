<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'name',
        'description',
        'client_id',
        'phase',
        'nilai_proyek',
    ];

    public function administration()
    {
        return $this->hasOne(ProjectAdministration::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'project_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo(Perusahaan::class, 'id');
    }

    public function handover()
    {
        return $this->hasOne(ProjectHandover::class, 'project_id', 'id');
    }
}