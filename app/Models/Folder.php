<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = ['nama', 'parent_id', 'is_pinned', 'is_archived', 'user_id', 'sort_order'];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_archived' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function pelamars()
    {
        return $this->hasMany(PelamarFolder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
