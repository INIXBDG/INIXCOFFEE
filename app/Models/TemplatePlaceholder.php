<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplatePlaceholder extends Model
{
    protected $table = 'template_placeholders';

    protected $fillable = [
        'template_id',
        'placeholder_key',
        'placeholder_label',
        'field_type',
        'is_manual',
        'source_column',
        'options',
        'default_value',
        'sort_order',
        'config'
    ];

    protected $casts = [
        'is_manual' => 'boolean',
        'options' => 'array',
        'sort_order' => 'integer',
        'config' => 'array'
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'template_id');
    }
}