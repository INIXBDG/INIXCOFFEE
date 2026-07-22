<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureDocumentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'category', 'status', 'short_description', 'purpose',
        'background', 'problem_solved', 'how_it_works', 'user_access',
        'manual_file_path', 'manual_file_name'
    ];

    public function codeDocumentations(): HasMany
    {
        return $this->hasMany(CodeDocumentation::class);
    }

    public function getStatusBadgeClassAttribute()
    {
        return [
            'draft' => 'bg-secondary',
            'development' => 'bg-warning',
            'production' => 'bg-success',
            'deprecated' => 'bg-danger'
        ][$this->status] ?? 'bg-secondary';
    }
}