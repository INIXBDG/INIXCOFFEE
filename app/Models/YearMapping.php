<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class YearMapping extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'year',
        'quarter',
        'month',
        'theme',
        'planned_date',
        'duration_minutes'
    ];

    protected $casts = [
        'planned_date' => 'date',
    ];

    /**
     * RELASI
     */

    // 1. Ke Detail Event ("Big Event": Judul & Narasumber)
    public function eventDetail()
    {
        return $this->hasOne(QuarterEvent::class, 'year_mapping_id');
    }

    // 2. Ke Item Timeline Harian ("Small Items": Posting IG, Meeting, dll)
    public function timelineItems()
    {
        return $this->hasMany(TimelineItem::class, 'year_mapping_id');
    }

    // 3. Ke Checklist Operasional ("Todo List": Status Centang)
    public function checklists()
    {
        return $this->hasMany(EventTodo::class, 'year_mapping_id');
    }

    /**
     * LOGIKA HAPUS (BOOT)
     * Menghapus semua data anak jika data induk (Mapping) dihapus
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($mapping) {
            // Hapus detail event
            if ($mapping->eventDetail) {
                $mapping->eventDetail->delete();
            }

            // Hapus timeline items (gunakan query builder delete agar hemat memori)
            $mapping->timelineItems()->delete();

            // Hapus checklist
            $mapping->checklists()->delete();
        });
    }
}
