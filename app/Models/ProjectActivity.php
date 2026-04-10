<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Karyawan; // Pastikan model Karyawan diimpor

class ProjectActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_task_id',
        'user_id', // Pastikan data yang disimpan di sini adalah kode_karyawan saat proses insert
        'activity',
        'status',
        'activity_date',
        'doc',
    ];

    public function projecttask()
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id', 'id');
    }

    /**
     * Relasi ke entitas Karyawan sebagai pembuat aktivitas.
     */
    public function user()
    {
        // Menghubungkan user_id pada tabel project_activities dengan kode_karyawan pada tabel karyawans
        return $this->belongsTo(Karyawan::class, 'user_id', 'kode_karyawan');
    }
}