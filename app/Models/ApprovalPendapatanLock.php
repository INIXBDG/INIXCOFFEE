<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalPendapatanLock extends Model
{
    use HasFactory;

    protected $table = 'approval_pendapatan_locks';
    protected $fillable = ['password', 'created_by', 'updated_by'];
    protected $hidden = ['password'];
}