<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Karyawan; // Pastikan model Karyawan diimpor

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
        'assignee_id',
        'current_stage',
        'surat_pekerjaan_dimulai_file',
        'proposal_file',
        'project_handover_id',
    ];

    /**
     * Konversi tipe data atribut.
     *
     * @var array
     */
    protected $casts = [
        'assignee_id' => 'array',
        'client_doc_file' => 'array', // ✅ TAMBAH INI
    ];

    public function dataproject()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * Relasi ke entitas Karyawan sebagai Project Manager.
     */
    public function projectManager()
    {
        return $this->belongsTo(Karyawan::class, 'pm_id', 'kode_karyawan');
    }

    public function project_handover()
    {
        return $this->belongsTo(ProjectHandover::class, 'project_handover_id', 'id');
    }

    /**
     * Accessor untuk mengambil koleksi Karyawan berdasarkan array assignee_id.
     * Dapat dipanggil menggunakan properti dinamis: $projectAdmin->assignees
     */
    public function getAssigneesAttribute()
    {
        if (empty($this->assignee_id)) {
            return collect(); // Mengembalikan koleksi kosong jika tidak ada data
        }
        return Karyawan::whereIn('kode_karyawan', $this->assignee_id)->get();
    }
}