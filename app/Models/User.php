<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Vinkla\Hashids\Facades\Hashids;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    protected $appends = ['hashids'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'jabatan',
        'status_akun',
        'password',
        'id_instruktur',
        'id_sales',
        'karyawan_id',
        'ttd',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'karyawan_id', 'id');
    }

    public function isAdmin()
    {
        return $this->jabatan === 'HRD';
    }

    public function getHashidsAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function surveyKepuasan()
    {
        return $this->hasMany(SurveyKepuasan::class, 'id_user');
    }

    public function kondisiKendaraan(){
        return $this->hasMany(KondisiKendaraan::class, 'user_id', 'id');
    }
}
