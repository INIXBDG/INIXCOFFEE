<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistKeperluan extends Model
{
    use HasFactory;

    protected $table = 'checklist_keperluans';

    protected $fillable = [
        'id_rkm',
        'materi',
        'kelas',
        'cb',
        'maksi',
        'keperluan_kelas',
    ];

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }
}
