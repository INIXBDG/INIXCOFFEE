<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentSchedule extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model ini.
     */
    protected $table = 'content_schedules';

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignable).
     */
    protected $fillable = [
        'content_form',
        'upload_date',
        'talents',
        'description',
        'proof_script',
        'proof_image_path',
        'is_tiktok',
    ];

    /**
     * Atribut yang harus dikonversi ke tipe data asli (Casting).
     */
    protected $casts = [
        'upload_date' => 'date',
        'is_tiktok' => 'boolean',
    ];
}
