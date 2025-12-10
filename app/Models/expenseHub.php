<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class expenseHub extends Model
{
    use HasFactory;

    protected $fillable = ['id_karyawan', 'tipe', 'status', 'invoice', 'id_rkm'];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_karyawan');
    }

    public function detailExpenseHub()
    {
        return $this->hasMany(detailExpenseHub::class, 'id_expenseHub');
    }

    public function trackingExpenseHub()
    {
        return $this->hasMany(trackingExpenseHub::class, 'id_expenseHub', 'id');
    }

    public function RKM()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }
}
