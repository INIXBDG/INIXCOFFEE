<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Notifications\Notifiable;
use Vinkla\Hashids\Facades\Hashids;

class karyawan extends Model
{
    use HasFactory;
    use Notifiable;
    protected $appends = ['hashids'];

    protected $fillable = ['foto', 'nip', 'nama_lengkap', 'email', 'divisi', 'jabatan', 'rekening_maybank', 'rekening_bca', 'status_aktif', 'awal_probation', 'akhir_probation', 'awal_kontrak', 'akhir_kontrak', 'awal_tetap', 'akhir_tetap', 'keterangan', 'kode_karyawan', 'ttd', 'cuti', 'email', 'whatsapp', 'telepon', 'gaji', 'alamat_lengkap', 'gender', 'tempat_lahir', 'tanggal_lahir', 'religion', 'provinsi', 'kota', 'resigned_at', 'alasan_resign'];

    public function user()
    {
        return $this->hasOne(User::class, 'karyawan_id');
    }
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    public function formPenilaian()
    {
        return $this->hasMany(formPenilaian::class, 'id_karyawan', 'id');
    }

    public function perusahaan()
    {
        return $this->hasOne(Perusahaan::class, 'karyawan_key', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'karyawan_key', 'id');
    }

    public function rkmsSales()
    {
        return $this->hasMany(Rkm::class, 'sales_key', 'kode_karyawan');
    }

    public function rkmsInstruktur()
    {
        return $this->hasMany(RKM::class, 'instruktur_key', 'kode_karyawan');
    }

    public function rkmsInstruktur2()
    {
        return $this->hasMany(Rkm::class, 'instruktur_key2', 'kode_karyawan');
    }

    public function rkmsAsisten()
    {
        return $this->hasMany(Rkm::class, 'asisten_key', 'kode_karyawan');
    }
    protected function image(): Attribute
    {
        return Attribute::make(get: fn($foto) => url('/storage/posts/' . $foto));
    }

    public function tunjangankaryawan()
    {
        return $this->hasMany(TunjanganKaryawan::class);
    }

    public function lembur()
    {
        return $this->hasMany(lembur::class);
    }

    public function getHashidsAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function pickupDriver()
    {
        return $this->hasMany(pickupDriver::class, 'id_karyawan');
    }

    public function educations()
    {
        return $this->hasMany(EducationalBackground::class, 'kode_karyawan', 'kode_karyawan');
    }

    public function specializations()
    {
        return $this->hasMany(SpecializationArea::class, 'kode_instruktur', 'kode_karyawan');
    }

    public function laporanSales()
    {
        return $this->hasMany(LaporanHarianSales::class);
    }
    public function catatanMeetingSales()
    {
        return $this->hasMany(CatatanMeetingSales::class);
    }

    public function absensi()
    {
        return $this->hasMany(AbsensiKaryawan::class, 'id_karyawan', 'id');
    }

    public function cuti()
    {
        return $this->hasMany(pengajuancuti::class, 'id_karyawan', 'id');
    }

    public function administrasiKaryawan()
    {
        return $this->hasMany(AdministrasiKaryawan::class, 'id_karyawan');
    }

    public function jabatan()
    {
        return $this->belongsToMany(OrgStructure::class, 'karyawan_jabatan')->withPivot('is_primary')->withTimestamps();
    }

    public function jobProfile()
    {
        return $this->hasOne(JobProfile::class, 'karyawan_id', 'id');
    }

    public function getOrgStructureAttribute()
    {
        return OrgStructure::whereJsonContains('karyawan_ids', (int) $this->id)->first();
    }

    public function logGaji()
    {
        return $this->hasMany(LogGaji::class, 'id_karyawan', 'id');
    }

    public function getSeminarAndEventsAttribute()
    {
        return $this->rkmsInstruktur->where('event', '!=', 'Kelas');
    }

    public function getTeachingExperiencesAttribute()
    {
        $rkms = $this->rkmsInstruktur->where('event', 'Kelas');

        return $rkms->groupBy('materi_key')->map(function ($group) {
            $materiName = optional($group->first()->materi)->nama_materi ?? 'Unknown Course';

            $companies = $group->map(function ($rkm) {
                // Gunakan nama perusahaan atau 'Personal' jika null
                return optional($rkm->perusahaan)->nama_perusahaan ?? 'Personal';
            })->unique()->values();

            $minYear = $group->min(function ($rkm) {
                return $rkm->tanggal_awal ? \Carbon\Carbon::parse($rkm->tanggal_awal)->format('Y') : date('Y');
            });

            return (object)[
                'course_name' => $materiName,
                'companies'   => $companies,
                'year_period' => $minYear . '-now',
            ];
        })->values();
    }
}
