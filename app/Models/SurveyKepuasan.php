<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Tickets;
use App\Models\karyawan;

class SurveyKepuasan extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'id_user',
        'ticket_id',
        'q1',
        'q2',
        'q3',
        'q4',
        'q5',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function ticket()
    {
        return $this->belongsTo(Tickets::class, 'ticket_id', 'ticket_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'id_user', 'id');
    }

    public function getNamaUserAttribute()
    {
        if ($this->ticket && !empty($this->ticket->nama_karyawan)) {
            return $this->ticket->nama_karyawan;
        }
        if ($this->user && $this->user->karyawan && !empty($this->user->karyawan->nama_lengkap)) {
            return $this->user->karyawan->nama_lengkap;
        }
        if ($this->karyawan && !empty($this->karyawan->nama_lengkap)) {
            return $this->karyawan->nama_lengkap;
        }
        return $this->user?->username ?? 'User';
    }
}
