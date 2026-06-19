<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Pelamar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pelamars';

    protected $fillable = ['nama_lengkap', 'email', 'no_telepon', 'domisili', 'tanggal_lahir', 'jenis_kelamin', 'pendidikan_terakhir', 'jurusan', 'institusi', 'ipk', 'divisi', 'jabatan', 'detail_jabatan', 'tanggal_melamar', 'sumber_lamaran', 'tahap_rekrutmen', 'status_aktif', 'keahlian', 'pengalaman_tahun', 'gaji_diharapkan', 'cv_path', 'portofolio_path', 'foto_path', 'jadwal_interview', 'metode_interview', 'link_meeting', 'lokasi_interview', 'interviewer', 'tahap_interview', 'gaji_ditawarkan', 'tunjangan_makan', 'tunjangan_transport', 'tanggal_mulai_kerja', 'status_kepegawaian', 'benefit_lainnya', 'tanggal_offer_dikirim', 'status_offer', 'rating', 'catatan_hr', 'catatan_internal', 'alasan_penolakan', 'nik_karyawan', 'atasan_langsung', 'checklist_onboarding', 'simpan_talent_pool', 'talent_pool_catatan', 'karyawan_id'];

    protected $casts = [
        'keahlian' => 'array',
        'checklist_onboarding' => 'array',
        'tanggal_melamar' => 'date',
        'tanggal_lahir' => 'date',
        'jadwal_interview' => 'datetime',
        'tanggal_mulai_kerja' => 'date',
        'tanggal_offer_dikirim' => 'datetime',
        'simpan_talent_pool' => 'boolean',
        'status_aktif' => 'boolean',
        'gaji_diharapkan' => 'integer',
        'gaji_ditawarkan' => 'integer',
        'tunjangan' => 'integer',
        'rating' => 'integer',
        'pengalaman_tahun' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    const TAHAP = [
        'applied' => 'Lamaran Masuk',
        'screening' => 'Screening',
        'interview' => 'Interview',
        'offer' => 'Offer',
        'hired' => 'Diterima',
        'rejected' => 'Ditolak',
    ];

    const HARI_KERJA_PER_BULAN = 22;

    const SUMBER = ['LinkedIn', 'JobStreet', 'Website Perusahaan', 'Referral Karyawan', 'Glints', 'Kalibrr', 'Instagram', 'Walk-in Interview', 'Job Fair', 'Kampus', 'Lainnya'];

    const PENDIDIKAN = ['SMA/SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'];

    const STATUS_KEPEGAWAIAN = [
        'probation' => 'Probation',
        'pkwt' => 'Kontrak (PKWT)',
        'pkwtt' => 'Karyawan Tetap (PKWTT)',
    ];

    const STATUS_OFFER = [
        'pending' => 'Menunggu',
        'accepted' => 'Diterima',
        'rejected' => 'Ditolak',
    ];

    const METODE_INTERVIEW = [
        'online' => 'Online (Zoom / Google Meet)',
        'offline' => 'Offline (Datang ke Kantor)',
        'phone' => 'Telepon',
    ];

    const TAHAP_INTERVIEW = [
        'hr' => 'Interview HR',
        'user' => 'Interview Manager/SPV/Koordinator',
        'technical' => 'Technical Test',
        'direksi' => 'Interview Direksi',
    ];

    const ALASAN_PENOLAKAN = [
        'kualifikasi' => 'Kualifikasi tidak sesuai',
        'pengalaman' => 'Pengalaman kurang memadai',
        'interview' => 'Hasil interview tidak memenuhi',
        'posisi' => 'Posisi telah terisi',
        'lainnya' => 'Alasan lainnya',
    ];

    const CHECKLIST_ONBOARDING_DEFAULT = [
        'kontrak_ditandatangani' => 'Kontrak kerja ditandatangani',
        'dokumen_pribadi' => 'Dokumen pribadi lengkap',
        'akun_sistem' => 'Akun email & sistem perusahaan dibuat',
        'perangkat_kerja' => 'Perangkat kerja disiapkan',
        'bpjs' => 'Pendaftaran BPJS',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function riwayatTahap()
    {
        return $this->hasMany(PelamarRiwayat::class, 'pelamar_id')->orderBy('created_at', 'asc');
    }

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    public function scopeTahap($query, $tahap)
    {
        return $query->where('tahap_rekrutmen', $tahap);
    }

    public function scopeTalentPool($query)
    {
        return $query->where('simpan_talent_pool', true);
    }

    public function scopeCariNama($query, $keyword)
    {
        return $query->where('nama_lengkap', 'like', "%{$keyword}%");
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $kw = $filters['search'];
            $query->where(function ($q) use ($kw) {
                $q->where('nama_lengkap', 'like', "%{$kw}%")
                    ->orWhere('email', 'like', "%{$kw}%")
                    ->orWhere('jabatan', 'like', "%{$kw}%")
                    ->orWhere('detail_jabatan', 'like', "%{$kw}%");
            });
        }

        if (!empty($filters['divisi'])) {
            $query->where('divisi', $filters['divisi']);
        }

        if (!empty($filters['jabatan'])) {
            $query->where('jabatan', $filters['jabatan']);
        }

        if (!empty($filters['tahap'])) {
            $query->where('tahap_rekrutmen', $filters['tahap']);
        }

        if (!empty($filters['sumber'])) {
            $query->where('sumber_lamaran', $filters['sumber']);
        }

        if (!empty($filters['tanggal_dari'])) {
            $query->whereDate('tanggal_melamar', '>=', $filters['tanggal_dari']);
        }

        if (!empty($filters['tanggal_sampai'])) {
            $query->whereDate('tanggal_melamar', '<=', $filters['tanggal_sampai']);
        }

        if (!empty($filters['talent_pool'])) {
            $query->where('simpan_talent_pool', true);
        }

        return $query;
    }

    public function getTahapLabelAttribute(): string
    {
        return self::TAHAP[$this->tahap_rekrutmen] ?? ucfirst($this->tahap_rekrutmen);
    }

    public function getInisialAttribute(): string
    {
        $parts = explode(' ', trim($this->nama_lengkap));
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper($part[0] ?? '');
        }
        return $initials;
    }

    public function getCvUrlAttribute(): ?string
    {
        return $this->cv_path ? Storage::url($this->cv_path) : null;
    }

    public function getPortofolioUrlAttribute(): ?string
    {
        return $this->portofolio_path ? Storage::url($this->portofolio_path) : null;
    }

    public function getUsiaAttribute(): ?int
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->age : null;
    }

    public function getGajiDiharapkanFormatAttribute(): string
    {
        return $this->gaji_diharapkan ? 'Rp ' . number_format($this->gaji_diharapkan, 0, ',', '.') : '-';
    }

    public function getGajiDitawarkanFormatAttribute(): string
    {
        return $this->gaji_ditawarkan ? 'Rp ' . number_format($this->gaji_ditawarkan, 0, ',', '.') : '-';
    }

    public function getSudahDijadwalkanAttribute(): bool
    {
        return !is_null($this->jadwal_interview);
    }

    public function tahapBerikutnya(): ?string
    {
        $urutan = ['applied', 'screening', 'interview', 'offer', 'hired'];
        $current = array_search($this->tahap_rekrutmen, $urutan);
        return $current !== false && isset($urutan[$current + 1]) ? $urutan[$current + 1] : null;
    }

    public function bisaLanjut(): bool
    {
        return !in_array($this->tahap_rekrutmen, ['hired', 'rejected']);
    }

    public function sudahDiterima(): bool
    {
        return $this->tahap_rekrutmen === 'hired';
    }

    public function sudahDitolak(): bool
    {
        return $this->tahap_rekrutmen === 'rejected';
    }

    public function progressPersen(): int
    {
        $map = ['applied' => 10, 'screening' => 30, 'interview' => 55, 'offer' => 75, 'hired' => 100, 'rejected' => 0];
        return $map[$this->tahap_rekrutmen] ?? 0;
    }

    public static function statsFunnel(): array
    {
        return [
            'total' => self::aktif()->count(),
            'screening' => self::aktif()->tahap('screening')->count(),
            'interview' => self::aktif()->tahap('interview')->count(),
            'offer' => self::aktif()->tahap('offer')->count(),
            'hired' => self::aktif()->tahap('hired')->count(),
            'rejected' => self::aktif()->tahap('rejected')->count(),
        ];
    }

    public function pelamarFolders()
    {
        return $this->hasMany(PelamarFolder::class);
    }

    public function getInterviewerListAttribute()
    {
        if (!$this->interviewer) {
            return [];
        }

        if (str_starts_with($this->interviewer, '[')) {
            return json_decode($this->interviewer, true) ?? [];
        }

        return explode(',', $this->interviewer);
    }

    public function getInterviewerDisplayAttribute()
    {
        $list = $this->interviewer_list;
        if (empty($list)) {
            return '-';
        }

        if (count($list) <= 2) {
            return implode(', ', $list);
        }

        return $list[0] . ' dkk (' . count($list) . ' orang)';
    }
}
