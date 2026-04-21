<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanKlaimModul extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_klaim_modul';

    protected $fillable = [
        'module_id',
        'price',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}