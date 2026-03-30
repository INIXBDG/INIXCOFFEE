<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAdministration extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'kak_file',
        'budget_file',
        'legal_file',
        'client_doc_file',
        'payment_doc_file',
        'pm_id',
        'current_stage',
    ];

    public function dataproject()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function projectManager()
    {
        return $this->belongsTo(Karyawan::class, 'pm_id', 'kode_karyawan');
    }
}