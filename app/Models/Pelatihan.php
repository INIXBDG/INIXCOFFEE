<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelatihan extends Model
{
    use HasFactory;

    protected $table = 'pelatihans';

    protected $fillable = [
        'user_id',
        'nama_pelatihan',
        'penyedia',
        'tanggal_mulai', 
        'tanggal_selesai',
        'keterangan',
        'harga',
        'status_approval',
        'approved_by',
        'approved_at',
    ];

    /**
     * Mengambil data peserta pelatihan
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mengambil data manager yang melakukan approval
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}