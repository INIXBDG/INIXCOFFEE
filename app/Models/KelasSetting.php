<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelasSetting extends Model
{
    use HasFactory;

    protected $fillable = ['id_rkm', 'week_start', 'week_end', 'kelas', 'dari', 'sampai', 'ruangan', 'device', 'device_instruktur', 'pax', 'instruktur', 'pc_its', 'asset', 'software', 'keterangan', 'status', 'comments'];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'dari' => 'date',
        'sampai' => 'date',
        'pax' => 'integer',
        'comments' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'comments' => '{}',
        'status' => 'Biru',
        'pax' => 0,
    ];

    public function getCommentsForField(string $field): array
    {
        return $this->comments[$field] ?? [];
    }

    public function addComment(string $field, ?string $author, string $text): self
    {
        $comments = $this->comments ?? [];
        if (!isset($comments[$field])) {
            $comments[$field] = [];
        }
        $comments[$field][] = [
            'id' => 'c' . uniqid(),
            'author' => $author ?: 'Anonymous',
            'text' => $text,
        ];
        $this->comments = $comments;
        return $this;
    }

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }

    public function removeComment(string $field, string $commentId): self
    {
        $comments = $this->comments ?? [];
        if (isset($comments[$field])) {
            $comments[$field] = collect($comments[$field])->reject(fn($c) => ($c['id'] ?? '') === $commentId)->values()->toArray();
        }
        $this->comments = $comments;
        return $this;
    }
}
