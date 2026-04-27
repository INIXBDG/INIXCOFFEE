<?php

namespace App\Imports;

use App\Models\karyawan;
use App\Models\TargetKPI;
use App\Models\DetailTargetKPI;
use App\Models\DetailPersonKPI;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class KpiTargetImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, WithBatchInserts, SkipsOnError, SkipsOnFailure
{
    protected $userId;
    protected $options;
    protected $existingTargets;
    protected $karyawanCache;
    protected $validRoutes;
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];

    // Valid routes yang diizinkan (sesuaikan dengan aplikasi Anda)
    private const VALID_ROUTES = [
        // ================= GM =================
        'pemasukan bersih',
        'kepuasan pelanggan',
        'rasio biaya operasional terhadap revenue',
        'performa kpi departemen',

        // ================= CS =================
        'peserta puas dengan pelayanan dan fasilitas training',
        'penanganan komplain peserta',
        'report persiapan kelas',

        // ================= Finance =================
        'outstanding',
        'penyelesaian tagihan perusahaan',
        'akurasi pencatatan masuk',
        'pencairan biaya operasional',

        // ================= HRD =================
        'pelaksanaan kegiatan karyawan',
        'pengeluaran biaya karyawan',
        'administrasi karyawan',

        // ================= Driver =================
        'perbaikan kendaraan',
        'report kondisi kendaraan',
        'kontrol pengeluaran transportasi',
        'feedback kenyamanan berkendaran',

        // ================= OB =================
        'feedback kebersihan dan kenyamanan',
        'penyelesaian tugas harian',

        // ================= ITSM =================
        'kepuasan client itsm',
        'meningkatkan kepuasan dan loyalitas peserta/client',
        'availability sistem internal kritis',

        // ================= Programmer =================
        'ketepatan waktu penyelesaian fitur',
        'mengukur kualitas aplikasi agar minim bug',

        // ================= Digital =================
        'konsistensi campaign digital',

        // ================= TS =================
        'keberhasilan support memenuhi sla',
        'kualitas layanan exam',

        // ================= Instruktur =================
        'presentase kinerja instruktur',
        'kepuasan peserta pelatihan',
        'upseling lanjutan materi',

        // ================= Manager Edu =================
        'peningkatan kontribusi pelatihan',
        'evaluasi kinerja instruktur',

        // ================= Sales =================
        'target penjualan tahunan',
        'biaya akuisisi perclient',

        // ================= SPV Sales =================
        'customer acquisition cost',
        'evaluasi kinerja sales',

        // ================= Adm Sales =================
        'laporan mom',
        'akurasi kelengkapan data penjualan',
        'todo administrasi',

        // ================= Adm Holding =================
        'ketepatan waktu po',
        'kualitas dokumentasi support dan proctor',

        // ================= Kombinasi =================
        'persentase gap kompetensi tim terhadap standar skill',
        'peningkatan kemampuan kompetensi sales',
        // GM
        'pemasukan kotor',

        // SPV Sales
        'meningkatkan revenue perusahaan',
        // CS
        'dorong inovasi pelayanan',

        // Finance
        'inisiatif efisiensi keuangan',
        'mengurangi manual work dan error',
        'laporan analisis keuangan',

        // Digital
        'efektifitas digital marketing',

        // Instruktur
        'sertifikasi kompetensi internal',
        'pelatihan kompetensi eksternal',

        // Manager Edu
        'pengembangan kurikulum pelatihan',
        'peningkatan knowledge sharing',

        // Kombinasi IT
        'inovation adaption rate',
    ];

    private const VALID_JANGKA = ['tahunan', 'bulanan', 'kuartalan', 'mingguan'];
    private const VALID_TIPE = ['rupiah', 'persen', 'angka'];

    public function __construct(array $options = [])
    {
        $this->userId = Auth::id();
        $this->options = $options;
        $this->preloadData();
    }

    /**
     * Pre-load data untuk optimasi performa (hindari N+1 query)
     */
    private function preloadData(): void
    {
        // Cache semua karyawan aktif
        $this->karyawanCache = karyawan::select('id', 'nama_lengkap', 'jabatan', 'divisi', 'kode_karyawan')->where('status_aktif', '1')->orWhereNull('status_aktif')->get()->groupBy('jabatan');

        // Cache target yang sudah ada untuk deteksi duplikat
        if ($this->options['skip_duplicate'] ?? false) {
            $this->existingTargets = TargetKPI::where('id_pembuat', $this->userId)->whereYear('created_at', now()->year)->pluck('judul', 'judul');
        }

        // Valid routes lowercase untuk comparison
        $this->validRoutes = array_map('strtolower', self::VALID_ROUTES);
    }

    /**
     * Mapping Excel row ke Model
     */
    public function model(array $row)
    {
        try {
            return DB::transaction(function () use ($row) {
                // Normalisasi data
                $data = $this->normalizeRow($row);

                // Validasi bisnis tambahan
                $this->validateBusinessRules($data);

                // Cek duplikat jika opsi aktif
                if (($this->options['skip_duplicate'] ?? false) && $this->isDuplicate($data['judul_kpi'])) {
                    $this->skippedCount++;
                    return null; // Skip, tidak insert
                }

                // 1. Insert Target KPI Utama
                $targetKPI = TargetKPI::create([
                    'id_assistant' => null,
                    'id_pembuat' => $this->userId,
                    'judul' => $data['judul_kpi'],
                    'deskripsi' => $data['deskripsi_kpi'],
                    'asistant_route' => $data['asistant_route'],
                    'status' => '0',
                ]);

                // 2. Process setiap jabatan
                $jabatanList = explode(',', $data['jabatan']);

                foreach ($jabatanList as $jabatan) {
                    $jabatan = trim($jabatan);
                    if (empty($jabatan)) {
                        continue;
                    }

                    // Ambil divisi dari cache karyawan
                    $divisi = $this->getDivisiByJabatan($jabatan);

                    if (!$divisi) {
                        throw new \Exception("Jabatan '{$jabatan}' tidak terdaftar di sistem.");
                    }

                    // Insert Detail Target KPI
                    $detailTarget = DetailTargetKPI::create([
                        'id_targetKPI' => $targetKPI->id,
                        'jabatan' => $jabatan,
                        'divisi' => $divisi,
                        'jangka_target' => $data['jangka_target'],
                        'detail_jangka' => $data['detail_jangka'],
                        'tipe_target' => $data['tipe_target'],
                        'nilai_target' => $data['nilai_target'],
                    ]);

                    // 3. Assign karyawan jika ada
                    if (!empty($data['karyawan'])) {
                        $this->assignKaryawanToTarget($targetKPI->id, $detailTarget->id, $data['karyawan'], $jabatan);
                    }
                }

                $this->importedCount++;
                return $targetKPI;
            });
        } catch (\Exception $e) {
            Log::warning('Import KPI gagal baris #' . ($row['row_number'] ?? 'N/A'), [
                'error' => $e->getMessage(),
                'data' => array_slice($row, 0, 5), // Log sebagian saja untuk keamanan
            ]);
            throw $e; // Re-throw agar ditangani oleh SkipsOnError
        }
    }

    /**
     * Normalisasi dan sanitasi data dari Excel
     */
    private function normalizeRow(array $row): array
    {
        return [
            'judul_kpi' => $this->cleanString($row['judul_kpi'] ?? ''),
            'deskripsi_kpi' => $this->cleanString($row['deskripsi_kpi'] ?? ''),
            'jabatan' => $this->cleanString($row['jabatan'] ?? ''),
            'karyawan' => $this->cleanString($row['karyawan'] ?? ''),
            'jangka_target' => strtolower(trim($row['jangka_target'] ?? '')),
            'detail_jangka' => trim($row['detail_jangka'] ?? ''),
            'tipe_target' => strtolower(trim($row['tipe_target'] ?? '')),
            'nilai_target' => $this->parseNumeric($row['nilai_target'] ?? 0),
            'asistant_route' => strtolower(trim($row['asistant_route'] ?? '')),
        ];
    }

    /**
     * Validasi bisnis yang tidak bisa ditangani oleh rules()
     */
    private function validateBusinessRules(array $data): void
    {
        
        if (!in_array($data['jangka_target'], self::VALID_JANGKA)) {
            throw new \Exception("Jangka target '{$data['jangka_target']}' tidak valid. Gunakan: " . implode(', ', self::VALID_JANGKA));
        }

        if (!in_array($data['tipe_target'], self::VALID_TIPE)) {
            throw new \Exception("Tipe target '{$data['tipe_target']}' tidak valid. Gunakan: " . implode(', ', self::VALID_TIPE));
        }

        if (!in_array($data['asistant_route'], $this->validRoutes)) {
            throw new \Exception("Assistant route '{$data['asistant_route']}' tidak terdaftar.");
        }

        // Validasi nilai_target berdasarkan tipe
        if ($data['tipe_target'] === 'rupiah' && $data['nilai_target'] < 0) {
            throw new \Exception('Nilai target rupiah tidak boleh negatif.');
        }

        if (in_array($data['tipe_target'], ['persen', 'angka']) && ($data['nilai_target'] < 0 || $data['nilai_target'] > 100)) {
            throw new \Exception('Nilai target persen/angka harus antara 0-100.');
        }

        // Validasi format detail_jangka berdasarkan jangka_target
        $this->validateDetailJangka($data['jangka_target'], $data['detail_jangka']);
    }

    /**
     * Validasi format detail_jangka
     */
    private function validateDetailJangka(string $jangka, string $detail): void
    {
        $errors = [
            'tahunan' => 'Format tahun harus 4 digit (contoh: 2024)',
            'bulanan' => 'Format bulan harus MM-YYYY (contoh: 01-2024)',
            'kuartalan' => 'Format kuartal harus QX-YYYY (contoh: Q1-2024)',
            'mingguan' => 'Format minggu harus YYYYWXX (contoh: 2024W01)',
        ];

        $patterns = [
            'tahunan' => '/^\d{4}$/',
            'bulanan' => '/^\d{2}-\d{4}$/',
            'kuartalan' => '/^Q[1-4]-\d{4}$/i',
            'mingguan' => '/^\d{4}W\d{2}$/i',
        ];

        if (!preg_match($patterns[$jangka] ?? '/^.*$/', $detail)) {
            throw new \Exception($errors[$jangka] ?? 'Format detail jangka tidak valid.');
        }
    }

    /**
     * Cek duplikat berdasarkan judul + pembuat + tahun
     */
    private function isDuplicate(string $judul): bool
    {
        return $this->existingTargets?->has($judul) ?? false;
    }

    /**
     * Ambil divisi dari cache berdasarkan jabatan
     */
    private function getDivisiByJabatan(string $jabatan): ?string
    {
        $karyawanByJabatan = $this->karyawanCache->get($jabatan);

        if ($karyawanByJabatan?->isNotEmpty()) {
            // Ambil divisi pertama yang ditemukan (asumsi 1 jabatan = 1 divisi)
            return $karyawanByJabatan->first()->divisi;
        }

        return null;
    }

    /**
     * Assign karyawan ke target dengan validasi
     */
    private function assignKaryawanToTarget(int $targetId, int $detailTargetId, string $karyawanInput, string $jabatan): void
    {
        $namaList = array_filter(array_map('trim', explode(',', $karyawanInput)));
        if (empty($namaList)) {
            return;
        }

        $karyawanByJabatan = $this->karyawanCache->get($jabatan);
        if (!$karyawanByJabatan) {
            Log::warning("Tidak ada karyawan untuk jabatan: {$jabatan}");
            return;
        }

        $personData = [];
        foreach ($namaList as $nama) {
            $karyawan = $karyawanByJabatan->firstWhere('nama_lengkap', $nama);

            if ($karyawan) {
                $personData[] = [
                    'id_target' => $targetId,
                    'detailTargetKey' => $detailTargetId,
                    'id_karyawan' => $karyawan->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                Log::warning("Karyawan '{$nama}' tidak ditemukan untuk jabatan {$jabatan}");
            }
        }

        if (!empty($personData)) {
            DetailPersonKPI::insertOrIgnore($personData); // Hindari duplicate key error
        }
    }

    /**
     * Helper: Bersihkan string dari karakter tidak diinginkan
     */
    private function cleanString(?string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value ?? ''));
    }

    /**
     * Helper: Parse numeric value (handle string dengan titik/koma)
     */
    private function parseNumeric($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Handle format Indonesia: "1.000,50" -> 1000.50
        $cleaned = str_replace(['.', ','], ['', '.'], (string) $value);
        return is_numeric($cleaned) ? (float) $cleaned : 0;
    }

    public function rules(): array
    {
        return [
            '*.judul_kpi' => 'required|string|max:255',
            '*.jabatan' => 'required|string|max:255',
            '*.jangka_target' => 'required|string',
            '*.detail_jangka' => 'required|max:50',
            '*.tipe_target' => 'required|string',
            '*.nilai_target' => 'required|numeric|min:0',
            '*.asistant_route' => 'required|string|max:255',
            '*.deskripsi_kpi' => 'nullable|string|max:500',
            '*.karyawan' => 'nullable|string|max:500',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.judul_kpi.required' => 'Judul KPI wajib diisi',
            '*.jabatan.required' => 'Jabatan wajib diisi',
            '*.jangka_target.required' => 'Jangka target wajib diisi',
            '*.detail_jangka.required' => 'Detail jangka wajib diisi',
            '*.tipe_target.required' => 'Tipe target wajib diisi',
            '*.nilai_target.required' => 'Nilai target wajib diisi',
            '*.nilai_target.numeric' => 'Nilai target harus angka',
            '*.asistant_route.required' => 'Assistant route wajib diisi',
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->errors[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
            ];
        }
    }

    public function onError(\Throwable $e): void
    {
        $this->errors[] = [
            'row' => 'unknown',
            'attribute' => 'system',
            'errors' => [$e->getMessage()],
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }
    public function batchSize(): int
    {
        return 50;
    }

    // ===== Getters untuk summary =====

    public function getSummary(): array
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errors,
        ];
    }
}
