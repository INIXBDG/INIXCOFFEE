<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class manajemenRuangan extends Model
{
    use HasFactory;

    protected $fillable = ['ruangan', 'tanggal', 'jam_mulai', 'jam_selesai', 'kebutuhan', 'keterangan'];

    
}