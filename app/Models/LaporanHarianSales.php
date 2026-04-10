<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanHarianSales extends Model
{
    use HasFactory;

    protected $fillable = [
       'tanggal_pelaksanaan',
       'waktu_pelaksanaan',
       'tempat_or_media',
       'jumlah_peserta_hadir',
       'jumlah_peserta_tidak_hadir',
       'alasan_peserta_tidak_hadir',
       'jenis_meeting',
       'pic',
       'notulis',
       'topic',
       'catatan',
       'is_draft',
    ];

    public function picMeeting()
    {
        return $this->belongsTo(karyawan::class, 'pic');
    }
    public function notulisMeeting()
    {
        return $this->belongsTo(karyawan::class, 'notulis');
    }
    public function catatanSales()
    {
        return $this->hasMany(CatatanMeetingSales::class, 'laporan_id');
    }
    public function catatanClient()
    {
        return $this->hasMany(CatatanClientSales::class, 'laporan_id');
    }
}
