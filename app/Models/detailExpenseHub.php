<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detailExpenseHub extends Model
{
    use HasFactory;

    protected $fillable = ['id_expenseHub', 'nama_pengajuan', 'jumlah', 'harga_pengajuan', 'keterangan'];
}
