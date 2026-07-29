<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeManagement extends Model
{
    use HasFactory;

    protected $table = 'knowledge_managements';

    protected $fillable = [
        'title',
        'category',
        'content',
        'file_path',
        'file_name',
        'created_by',
        'updated_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getAuthorNameAttribute()
    {
        if ($this->creator) {
            return $this->creator->karyawan->nama_lengkap ?? $this->creator->username ?? 'User';
        }
        return 'User';
    }

    public function getUpdaterNameAttribute()
    {
        if ($this->updater) {
            return $this->updater->karyawan->nama_lengkap ?? $this->updater->username ?? '-';
        }
        return '-';
    }
}
