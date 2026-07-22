<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeDocumentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_documentation_id', 'title', 'description',
        'flow_program', 'code_blocks', 'relations',
        'change_logs', 'future_development'
    ];

    protected $casts = [
        'flow_program' => 'array',
        'code_blocks' => 'array',
        'relations' => 'array',
        'change_logs' => 'array',
        'future_development' => 'array'
    ];

    public function featureDocumentation(): BelongsTo
    {
        return $this->belongsTo(FeatureDocumentation::class);
    }
}