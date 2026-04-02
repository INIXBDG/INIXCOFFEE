<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Karyawan; // Pastikan model Karyawan diimpor

class ProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'assignee_id', // Berisi kode_karyawan tunggal
        'status',
        'task_file',
        'startdate',
        'enddate',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relasi ke entitas Karyawan sebagai pelaksana tugas.
     */
    public function assignee()
    {
        return $this->belongsTo(Karyawan::class, 'assignee_id', 'kode_karyawan');
    }
}