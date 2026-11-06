<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstrukturOlRiwayat extends Model
{
    protected $table = 'instruktur_ol_riwayats';

    protected $fillable = [
        'instruktur_ol_id',
        'rkm_id',
    ];

    public function instruktur()
    {
        return $this->belongsTo(InstrukturOl::class, 'instruktur_ol_id');
    }

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'rkm_id');
    }
}