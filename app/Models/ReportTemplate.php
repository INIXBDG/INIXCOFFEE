<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportTemplate extends Model {
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'category', 'source_table', 'template_file_path',
        'available_fields', 'field_mappings', 'manual_fields', 'is_active', 'created_by', 'edited_text'
    ];

    protected $casts = [
        'available_fields' => 'array',
        'field_mappings' => 'array',
        'manual_fields' => 'array',
        'is_active' => 'boolean'
    ];

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ✅ Foreign key sudah sesuai: 'template_id'
    public function placeholders(): HasMany {
        return $this->hasMany(TemplatePlaceholder::class, 'template_id');
    }

    public function generations(): HasMany {
        return $this->hasMany(ReportGeneration::class, 'template_id');
    }

    public function getAvailableFieldsAttribute($value) {
        return json_decode($value ?? '[]', true);
    }

    public function getFieldMappingsAttribute($value) {
        return json_decode($value ?? '{}', true);
    }
}