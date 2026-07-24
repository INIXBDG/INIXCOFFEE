<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class FeatureDocumentation extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'name', 'category', 'status', 'short_description', 'purpose', 'problem_solved', 'how_it_works', 'user_access', 'update_by', 'log_update', 'log_time_update'];

    protected $casts = [
        'log_update' => 'array',
        'log_time_update' => 'array',
    ];

    protected $appends = ['document_version', 'last_updated_at', 'status_badge_class', 'revision_number', 'version_path'];

    public function getLastUpdatedAtAttribute(): ?string
    {
        if (empty($this->log_time_update)) {
            return $this->created_at ? $this->created_at->translatedFormat('d F Y H:i') : null;
        }

        $latestTime = is_array($this->log_time_update) ? $this->log_time_update[0] ?? null : $this->log_time_update;

        if (!$latestTime) {
            return $this->created_at ? $this->created_at->translatedFormat('d F Y H:i') : null;
        }

        return Carbon::parse($latestTime)->translatedFormat('d F Y H:i');
    }

    public function getRevisionNumberAttribute(): int
    {
        $logs = $this->log_update ?? [];
        return max(count($logs) - 1, 0);
    }

    public function getVersionPathAttribute(): string
    {
        return $this->buildVersionPath();
    }

    public function getDocumentVersionAttribute(): string
    {
        $version = $this->buildVersionPath();
        $revision = $this->revision_number;

        if ($revision > 0) {
            $version .= ".r{$revision}";
        }

        return $version;
    }

    public function getRootAncestor(): self
    {
        $current = $this;

        while ($current->parentFeature) {
            $current = $current->parentFeature;
        }

        return $current;
    }

    protected function buildVersionPath(): string
    {
        static $cache = [];
        $cacheKey = $this->id;

        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        if (!$this->parent_id) {
            $version = 'v' . ($cacheKey ?? null);
            $cache[$cacheKey] = $version;
            return $version;
        }

        $segments = [];
        $current = $this;
        $root = null;

        while ($current->parent_id) {
            $parent = $current->parentFeature;

            if (!$parent) {
                break;
            }

            $siblings = $parent->children()->orderBy('created_at')->orderBy('id')->pluck('id')->values();

            $index = $siblings->search($current->id);
            $segments[] = $index !== false ? $index + 1 : 1;

            $current = $parent;
        }

        $root = $current;
        $segments = array_reverse($segments);

        $version = 'v' . ($cacheKey ?? 1);

        if (!empty($segments)) {
            $version .= '.' . implode('.', $segments);
        }

        $cache[$cacheKey] = $version;

        return $version;
    }

    public function getFullVersionWithRevision(): string
    {
        return $this->document_version;
    }

    public function getVersionHistory(): array
    {
        $logs = $this->log_update ?? [];
        $times = $this->log_time_update ?? [];
        $history = [];

        foreach ($logs as $index => $userId) {
            $history[] = [
                'revision' => $index,
                'version' => $this->buildVersionPath() . ($index > 0 ? ".r{$index}" : ''),
                'updated_by' => $userId,
                'updated_at' => $times[$index] ?? null,
            ];
        }

        return array_reverse($history);
    }

    public function getNextRevisionVersion(): string
    {
        $nextRevision = $this->revision_number + 1;
        return $this->buildVersionPath() . ".r{$nextRevision}";
    }

    public function codeDocumentations(): HasMany
    {
        return $this->hasMany(CodeDocumentation::class, 'feature_documentation_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'update_by');
    }

    public function parentFeature(): BelongsTo
    {
        return $this->belongsTo(FeatureDocumentation::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(FeatureDocumentation::class, 'parent_id')->orderBy('created_at')->orderBy('id');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive', 'codeDocumentations', 'updater');
    }

    public function scopeParentsOnly($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeWithRevision($query, int $revision)
    {
        return $query->whereRaw('JSON_LENGTH(log_update) = ?', [$revision + 1]);
    }

    public function isDescendantOf(int $possibleAncestorId): bool
    {
        $current = $this->parentFeature;

        while ($current) {
            if ($current->id === $possibleAncestorId) {
                return true;
            }
            $current = $current->parentFeature;
        }

        return false;
    }

    public function allDescendantIds(): array
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->allDescendantIds());
        }

        return $ids;
    }

    public function getDepth(): int
    {
        $depth = 0;
        $current = $this;

        while ($current->parent_id) {
            $depth++;
            $current = $current->parentFeature;
            if (!$current) {
                break;
            }
        }

        return $depth;
    }

    public function getBreadcrumb(): Collection
    {
        $breadcrumb = collect();
        $current = $this;

        while ($current) {
            $breadcrumb->prepend([
                'id' => $current->id,
                'name' => $current->name,
                'version' => $current->document_version,
            ]);
            $current = $current->parentFeature;
        }

        return $breadcrumb;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return [
            'draft' => 'bg-secondary',
            'development' => 'bg-warning',
            'production' => 'bg-success',
            'deprecated' => 'bg-danger',
        ][$this->status] ?? 'bg-secondary';
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'draft' => 'Draft',
            'development' => 'Dalam Pengembangan',
            'production' => 'Produksi',
            'deprecated' => 'Tidak Digunakan',
        ][$this->status] ?? 'Tidak Diketahui';
    }

    public function incrementRevision(): self
    {
        $userId = auth()->id();

        $logUpdate = $this->log_update ?? [];
        $logTimeUpdate = $this->log_time_update ?? [];

        array_unshift($logUpdate, $userId);
        array_unshift($logTimeUpdate, now()->toDateTimeString());

        $this->update([
            'update_by' => $userId,
            'log_update' => $logUpdate,
            'log_time_update' => $logTimeUpdate,
        ]);

        return $this->fresh();
    }

    protected static function booted()
    {
        static::creating(function ($feature) {
            $userId = auth()->id();
            $feature->update_by = $userId;
            $feature->log_update = [$userId];
            $feature->log_time_update = [now()->toDateTimeString()];
        });

        static::updating(function ($feature) {
            if ($feature->isDirty('id')) {
                $feature->log_update = [auth()->id()];
                $feature->log_time_update = [now()->toDateTimeString()];
            }
        });
    }
}
