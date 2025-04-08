<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rekapMengajarInstruktur extends Model
{
    use HasFactory;
    protected $fillable = [
            'id_rkm',  
            'id_instruktur',  
            'feedback',  
            'pax',  
            'level',  
            'durasi',  
            'tanggal_awal',
            'tanggal_akhir',
            'bulan',  
            'tahun',  
            'poin_durasi',  
            'poin_pax',  
            'tunjangan_feedback',  
            'total_tunjangan',  
            'status',  
            'keterangan',  
    ];

    public function rkm()
    {
        return $this->belongsTo(RKM::class, 'id_rkm', 'id');
    }

    public function instruktur()
    {
        return $this->belongsTo(karyawan::class, 'id_instruktur', 'kode_karyawan');
    }

    public function getFirstRKM()
    {
        // Mengambil id_rkm pertama sebelum koma
        $firstIdRKM = explode(',', $this->id_rkm)[0];
        return RKM::find($firstIdRKM);
    }
}
