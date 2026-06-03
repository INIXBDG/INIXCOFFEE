<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportGeneration extends Model
{
    use SoftDeletes;

    protected $fillable = ['template_id', 'report_title', 'source_type', 'source_id', 'manual_inputs', 'generated_data', 'output_file_path', 'status', 'generated_by'];

    protected $casts = [
        'manual_inputs' => 'array',
        'generated_data' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'template_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function getSourceModelAttribute()
    {
        $class = 'App\\Models\\' . ucfirst($this->source_type);
        return class_exists($class) ? $class::find($this->source_id) : null;
    }
}
