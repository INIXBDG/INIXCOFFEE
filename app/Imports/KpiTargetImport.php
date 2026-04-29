<?php

namespace App\Imports;

use App\Models\karyawan;
use App\Models\DataTarget;
use App\Models\targetKPI;
use App\Models\DetailTargetKPI;
use App\Models\detailPersonKPI;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\Importable;

class KpiTargetImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, WithStartRow
{
    use Importable;

    protected $options;
    protected $summary = [
        'imported' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    protected $routeMapping = [
        'GM' => ['Pemasukan Kotor', 'pemasukan bersih', 'Kepuasan Pelanggan', 'rasio biaya operasional terhadap revenue', 'performa KPI departemen'],
        'Customer Care' => ['peserta puas dengan pelayanan dan fasilitas training', 'dorong inovasi pelayanan', 'penanganan komplain perseta', 'report persiapan kelas'],
        'Finance & Accounting' => ['outstanding', 'inisiatif efisiensi keuangan', 'mengurangi manual work dan error', 'laporan analisis keuangan', 'pencairan biaya operasional', 'penyelesaian tagihan perusahaan', 'akurasi pencatatan masuk'],
        'HRD' => ['pelaksanaan kegiatan karyawan', 'pengeluaran biaya karyawan', 'administrasi karyawan'],
        'Driver' => ['perbaikan kendaraan', 'report kondisi kendaraan', 'kontrol pengeluaran transportasi', 'feedback kenyamanan berkendaran'],
        'Office Boy' => ['feedback kebersihan dan kenyamanan', 'penyelesaian tugas harian'],
        'Koordinator ITSM' => ['meningkatkan kepuasan dan loyalitas peserta/client', 'availability sistem internal kritis'],
        'Programmer' => ['ketepatan waktu penyelesaian fitur', 'mengukur kualitas aplikasi agar minim bug'],
        'Tim Digital' => ['konsistensi campaign digital', 'efektifitas diital marketing'],
        'Technical Support' => ['keberhasilan support memenuhi sla', 'kualitas layanan exam'],
        'Instruktur' => ['presentase kinerja instruktur', 'kepuasan peserta pelatihan', 'upseling lanjutan materi', 'sertifikasi kompetensi internal', 'pelatihan kompetensi eksternal'],
        'Education Manager' => ['pengembangan kurikulum pelatihan', 'peningkatan knowledge sharing', 'peningkatan kontribusi pelatihan', 'evaluasi kinerja instruktur'],
        'Sales' => ['target penjualan tahunan', 'biaya akuisisi perclient'],
        'SPV Sales' => ['meningkatkan revenue perusahaan', 'customer acquisition cost', 'evaluasi kinerja sales'],
        'Adm Sales' => ['laporan mom', 'akurasi kelengkapan data penjualan', 'todo administrasi'],
        'Admin Holding' => ['ketepatan waktu po', 'kualitas dokumentasi support dan proctor'],
    ];

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function startRow(): int
    {
        return 1;
    }

    public function sheet(): string
    {
        return 'Import';
    }

    public function rules(): array
    {
        return [
            'judul_kpi' => 'required|string|max:255',
            'deskripsi_kpi' => 'nullable|string|max:500',
            'jabatan' => 'required|string',
            'karyawan' => 'nullable|string',
            'asistant_route' => 'required|string',
            'detail_jangka' => 'nullable|string',
        ];
    }

    public function model(array $row)
    {
        try {
            if (empty(array_filter($row))) {
                return null;
            }

            $judul = trim($row['judul_kpi'] ?? '');
            $deskripsi = trim($row['deskripsi_kpi'] ?? '');
            $jabatanRaw = trim($row['jabatan'] ?? '');
            $karyawanRaw = trim($row['karyawan'] ?? '');
            $asistantRoute = trim($row['asistant_route'] ?? '');
            $detailJangkaInput = trim($row['detail_jangka'] ?? '');

            if (empty($judul) || empty($jabatanRaw) || empty($asistantRoute)) {
                if (empty($judul) && empty($jabatanRaw) && empty($asistantRoute)) {
                    return null;
                }
                throw new \Exception('Judul, Jabatan, dan Assistant Route wajib diisi');
            }

            $jabatanList = array_filter(array_map('trim', explode(',', $jabatanRaw)));
            if (empty($jabatanList)) {
                throw new \Exception('Format jabatan tidak valid');
            }

            $dataTarget = DataTarget::where('asistant_route', $asistantRoute)->first();
            if (!$dataTarget) {
                throw new \Exception("Assistant Route '{$asistantRoute}' tidak ditemukan dalam konfigurasi sistem");
            }

            $isValidRoute = $this->validateRouteForJabatan($jabatanList, $asistantRoute);
            if (!$isValidRoute) {
                throw new \Exception("Assistant Route '{$asistantRoute}' tidak valid untuk jabatan: " . implode(', ', $jabatanList));
            }

            $jangkaTarget = $dataTarget->jangka_target;
            $tipeTarget = $dataTarget->tipe_target;
            $nilaiTarget = $dataTarget->nilai_target;

            if ($jangkaTarget === 'Tahunan') {
                if (empty($detailJangkaInput)) {
                    throw new \Exception('Detail Jangka wajib diisi untuk target Tahunan (contoh: 2026)');
                }
                if (!preg_match('/^\d{4}$/', $detailJangkaInput)) {
                    throw new \Exception('Format Detail Jangka harus 4 digit tahun (contoh: 2026)');
                }
            }
            $detailJangkaValue = $jangkaTarget === 'Tahunan' ? $detailJangkaInput : null;

            if ($this->options['dry_run'] ?? false) {
                $this->summary['imported']++;
                return null;
            }

            return DB::transaction(function () use ($judul, $deskripsi, $jabatanList, $karyawanRaw, $asistantRoute, $dataTarget, $tipeTarget, $nilaiTarget, $jangkaTarget, $detailJangkaValue) {
                $idPembuat = Auth::id();

                if ($this->options['skip_duplicate'] ?? false) {
                    $exists = targetKPI::where('judul', $judul)->where('id_pembuat', $idPembuat)->where('asistant_route', $asistantRoute)->exists();

                    if ($exists) {
                        $this->summary['skipped']++;
                        return null;
                    }
                }

                $targetKPI = targetKPI::create([
                    'id_pembuat' => $idPembuat,
                    'id_data_target' => $dataTarget->id,
                    'judul' => $judul,
                    'deskripsi' => $deskripsi,
                    'asistant_route' => $asistantRoute,
                    'status' => '0',
                ]);

                foreach ($jabatanList as $jabatan) {
                    $dataDivisi = karyawan::where('jabatan', $jabatan)->where('divisi', '!=', 'Direksi')->value('divisi');

                    $detailTarget = DetailTargetKPI::create([
                        'id_targetKPI' => $targetKPI->id,
                        'jabatan' => $jabatan,
                        'divisi' => $dataDivisi,
                        'id_data_target' => $dataTarget->id,
                        'jangka_target' => $jangkaTarget,
                        'detail_jangka' => $detailJangkaValue,
                        'tipe_target' => $tipeTarget,
                        'nilai_target' => $nilaiTarget,
                    ]);

                    $karyawanIds = $this->resolveKaryawanIds($karyawanRaw, $jabatan);
                    foreach ($karyawanIds as $karyawanId) {
                        detailPersonKPI::create([
                            'id_target' => $targetKPI->id,
                            'detailTargetKey' => $detailTarget->id,
                            'id_karyawan' => $karyawanId,
                        ]);
                    }
                }

                $this->summary['imported']++;
                return $targetKPI;
            });
        } catch (\Exception $e) {
            $this->summary['errors'][] = "Error: {$e->getMessage()}";
            return null;
        }
    }

    protected function validateRouteForJabatan(array $jabatanList, string $route): bool
    {
        $kombinasiIT = ['Programmer', 'Tim Digital', 'Technical Support'];
        $kombinasiSales = ['Sales', 'SPV Sales', 'Adm Sales'];

        if (count(array_intersect($jabatanList, $kombinasiIT)) === 3) {
            $allowed = ['kepuasan client ITSM', 'inovation adaption rate', 'persentase gap kompetensi tim terhadap standar skill'];
            return in_array($route, $allowed);
        }

        if (count(array_intersect($jabatanList, $kombinasiSales)) === 3) {
            return $route === 'peningkatan kemampuan kompetensi sales';
        }

        foreach ($jabatanList as $jabatan) {
            if (isset($this->routeMapping[$jabatan]) && in_array($route, $this->routeMapping[$jabatan])) {
                return true;
            }
        }

        return false;
    }

    protected function resolveKaryawanIds(?string $karyawanRaw, string $jabatan): array
    {
        if (empty($karyawanRaw)) {
            return karyawan::where('jabatan', $jabatan)->where('divisi', '!=', 'Direksi')->where('status_aktif', '1')->pluck('id')->toArray();
        }

        $namaList = array_filter(array_map('trim', explode(',', $karyawanRaw)));
        $ids = [];

        foreach ($namaList as $nama) {
            $k = karyawan::where('nama_lengkap', 'LIKE', "%{$nama}%")
                ->where('jabatan', $jabatan)
                ->where('status_aktif', '1')
                ->first();
            if ($k) {
                $ids[] = $k->id;
            }
        }

        return empty($ids) ? karyawan::where('jabatan', $jabatan)->where('status_aktif', '1')->pluck('id')->toArray() : $ids;
    }

    public function getSummary(): array
    {
        return $this->summary;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
