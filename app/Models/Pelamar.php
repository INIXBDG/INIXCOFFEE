<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Pelamar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pelamars';

    public const HARI_KERJA_PER_BULAN = 22;

    public const TAHAP = [
        'applied' => 'Lamaran Masuk',
        'screening' => 'Screening',
        'psikotest' => 'Psikotest',
        'interview' => 'Interview',
        'offer' => 'Offer',
        'hired' => 'Diterima',
        'rejected' => 'Ditolak',
    ];

    public const URUTAN_TAHAP = ['applied', 'screening', 'psikotest', 'interview', 'offer', 'hired'];

    public const PROGRESS_TAHAP = [
        'applied' => 10,
        'screening' => 25,
        'psikotest' => 40,
        'interview' => 60,
        'offer' => 80,
        'hired' => 100,
        'rejected' => 0,
    ];

    public const SUMBER = [
        'LinkedIn',
        'JobStreet',
        'Website Perusahaan',
        'Referral Karyawan',
        'Glints',
        'Kalibrr',
        'Instagram',
        'Walk-in Interview',
        'Job Fair',
        'Kampus',
        'Lainnya',
    ];

    public const PENDIDIKAN = ['SMA/SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'];

    public const STATUS_KEPEGAWAIAN = [
        'probation' => 'Probation',
        'pkwt' => 'Kontrak (PKWT)',
        'pkwtt' => 'Karyawan Tetap (PKWTT)',
    ];

    public const STATUS_OFFER = [
        'pending' => 'Menunggu',
        'accepted' => 'Diterima',
        'rejected' => 'Ditolak',
    ];

    public const METODE_INTERVIEW = [
        'online' => 'Online (Zoom / Google Meet)',
        'offline' => 'Offline (Datang ke Kantor)',
        'phone' => 'Telepon',
    ];

    public const TAHAP_INTERVIEW = [
        'hr' => 'Interview HR',
        'user' => 'Interview Manager/SPV/Koordinator',
        'technical' => 'Technical Test',
        'direksi' => 'Interview Direksi',
    ];

    public const ALASAN_PENOLAKAN = [
        'kualifikasi' => 'Kualifikasi tidak sesuai',
        'pengalaman' => 'Pengalaman kurang memadai',
        'interview' => 'Hasil interview tidak memenuhi',
        'posisi' => 'Posisi telah terisi',
        'lainnya' => 'Alasan lainnya',
    ];

    public const CHECKLIST_ONBOARDING_DEFAULT = [
        'kontrak_ditandatangani' => 'Kontrak kerja ditandatangani',
        'dokumen_pribadi' => 'Dokumen pribadi lengkap',
        'akun_sistem' => 'Akun email & sistem perusahaan dibuat',
        'perangkat_kerja' => 'Perangkat kerja disiapkan',
        'bpjs' => 'Pendaftaran BPJS',
    ];

    protected $fillable = [
        'nama_lengkap',
        'email',
        'no_telepon',
        'nik_ktp',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'status_pernikahan',
        'agama',
        'domisili',
        'alamat',
        'linkedin_url',

        'pendidikan_terakhir',
        'jurusan',
        'institusi',
        'ipk',
        'pengalaman_tahun',
        'keahlian',

        'divisi',
        'jabatan',
        'detail_jabatan',
        'tanggal_melamar',
        'sumber_lamaran',
        'tahap_rekrutmen',
        'status_aktif',
        'gaji_diharapkan',
        'notice_period',

        'cv_path',
        'portofolio_path',
        'foto_path',
        'ktp_path',
        'ijazah_path',

        'jadwal_interview',
        'metode_interview',
        'link_meeting',
        'lokasi_interview',
        'interviewer',
        'tahap_interview',

        'gaji_ditawarkan',
        'tunjangan_makan',
        'tunjangan_transport',
        'tanggal_mulai_kerja',
        'status_kepegawaian',
        'benefit_lainnya',
        'tanggal_offer_dikirim',
        'status_offer',

        'rating',
        'catatan_hr',
        'catatan_internal',
        'alasan_penolakan',

        'nik_karyawan',
        'atasan_langsung',
        'checklist_onboarding',

        'simpan_talent_pool',
        'talent_pool_catatan',
        'karyawan_id',
    ];

    protected $casts = [
        'keahlian' => 'array',
        'checklist_onboarding' => 'array',
        'tanggal_melamar' => 'date',
        'tanggal_lahir' => 'date',
        'tanggal_mulai_kerja' => 'date',
        'jadwal_interview' => 'datetime',
        'tanggal_offer_dikirim' => 'datetime',
        'simpan_talent_pool' => 'boolean',
        'status_aktif' => 'boolean',
        'gaji_diharapkan' => 'integer',
        'gaji_ditawarkan' => 'integer',
        'tunjangan_makan' => 'integer',
        'tunjangan_transport' => 'integer',
        'rating' => 'integer',
        'pengalaman_tahun' => 'integer',
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'karyawan_id');
    }

    public function riwayatTahap()
    {
        return $this->hasMany(Pelamarriwayat::class, 'pelamar_id')->orderBy('created_at', 'asc');
    }

    public function penilaians()
    {
        return $this->hasMany(PelamarPenilaian::class);
    }

    public function pelamarFolders()
    {
        return $this->hasMany(PelamarFolder::class);
    }

    public function psikotests()
    {
        return $this->hasMany(PelamarPsikotest::class, 'pelamar_id');
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
        return self::TAHAP[$this->tahap_rekrutmen] ?? ucfirst((string) $this->tahap_rekrutmen);
    }

    public function getInisialAttribute(): string
    {
        $inisial = '';

        foreach (array_slice(explode(' ', trim($this->nama_lengkap)), 0, 2) as $kata) {
            $inisial .= strtoupper($kata[0] ?? '');
        }

        return $inisial;
    }

    public function getUsiaAttribute(): ?int
    {
        return $this->tanggal_lahir?->age;
    }

    public function getCvUrlAttribute(): ?string
    {
        return $this->cv_path ? Storage::url($this->cv_path) : null;
    }

    public function getPortofolioUrlAttribute(): ?string
    {
        return $this->portofolio_path ? Storage::url($this->portofolio_path) : null;
    }

    public function getGajiDiharapkanFormatAttribute(): string
    {
        return $this->formatRupiah($this->gaji_diharapkan);
    }

    public function getGajiDitawarkanFormatAttribute(): string
    {
        return $this->formatRupiah($this->gaji_ditawarkan);
    }

    public function getEstimasiPendapatanBulananAttribute(): int
    {
        $tunjanganHarian = (int) $this->tunjangan_makan + (int) $this->tunjangan_transport;

        return (int) $this->gaji_ditawarkan + ($tunjanganHarian * self::HARI_KERJA_PER_BULAN);
    }

    public function getEstimasiPendapatanBulananFormatAttribute(): string
    {
        return $this->formatRupiah($this->estimasi_pendapatan_bulanan);
    }

    public function getSudahDijadwalkanAttribute(): bool
    {
        return !is_null($this->jadwal_interview);
    }

    public function getInterviewerListAttribute(): array
    {
        if (!$this->interviewer) {
            return [];
        }

        if (str_starts_with($this->interviewer, '[')) {
            return json_decode($this->interviewer, true) ?? [];
        }

        return array_map('trim', explode(',', $this->interviewer));
    }

    public function getInterviewerDisplayAttribute(): string
    {
        $daftar = $this->interviewer_list;

        if (empty($daftar)) {
            return '-';
        }

        if (count($daftar) <= 2) {
            return implode(', ', $daftar);
        }

        return $daftar[0] . ' dkk (' . count($daftar) . ' orang)';
    }

    public function tahapBerikutnya(): ?string
    {
        $posisi = array_search($this->tahap_rekrutmen, self::URUTAN_TAHAP, true);

        if ($posisi === false) {
            return null;
        }

        return self::URUTAN_TAHAP[$posisi + 1] ?? null;
    }

    public function bisaLanjut(): bool
    {
        return !in_array($this->tahap_rekrutmen, ['hired', 'rejected'], true);
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
        return self::PROGRESS_TAHAP[$this->tahap_rekrutmen] ?? 0;
    }

    public static function statsFunnel(): array
    {
        return [
            'total' => self::aktif()->count(),
            'screening' => self::aktif()->tahap('screening')->count(),
            'psikotest' => self::aktif()->tahap('psikotest')->count(),
            'interview' => self::aktif()->tahap('interview')->count(),
            'offer' => self::aktif()->tahap('offer')->count(),
            'hired' => self::aktif()->tahap('hired')->count(),
            'rejected' => self::aktif()->tahap('rejected')->count(),
        ];
    }

    private function formatRupiah(?int $nilai): string
    {
        return $nilai ? 'Rp ' . number_format($nilai, 0, ',', '.') : '-';
    }
}