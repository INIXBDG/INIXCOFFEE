<?php

namespace App\Imports;

use App\Models\KategoriDaftarTugas;
use App\Models\KontrolTugas;
use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DaftarTugasImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsEmptyRows
{
    protected $userId;
    protected $jabatanPembuat;
    protected $stats = ['created' => 0, 'skipped' => 0, 'errors' => []];

    public function __construct($userId, $jabatanPembuat = null)
    {
        $this->userId = $userId;
        $this->jabatanPembuat = $jabatanPembuat;
    }

    /**
     * Parse tanggal dengan berbagai format
     */
    protected function parseDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }

        // Jika sudah instance Carbon/DateTime
        if ($dateValue instanceof \DateTimeInterface) {
            return Carbon::instance($dateValue)->toDateString();
        }

        // Jika angka (Excel date serial number)
        if (is_numeric($dateValue)) {
            try {
                return Carbon::createFromTimestampUTC(($dateValue - 25569) * 86400)->toDateString();
            } catch (\Exception $e) {
                return null;
            }
        }

        // Convert ke string dan bersihkan
        $dateString = trim((string) $dateValue);

        // Hapus karakter yang tidak perlu (strip, spasi berlebih, dll)
        $dateString = preg_replace('/\s+/', ' ', $dateString); // Normalize spaces
        $dateString = trim($dateString, " -\t\n\r\0\x0B"); // Trim special chars

        // Coba berbagai format tanggal
        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d', 'd F Y', 'd-m-Y H:i:s', 'Y-m-d H:i:s', 'd/m/Y H:i:s'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $dateString)->toDateString();
            } catch (\Exception $e) {
                continue;
            }
        }

        // Coba parse natural
        try {
            return Carbon::parse($dateString)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function model(array $row)
    {
        // Skip jika row kosong
        if (empty($row) || empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
            return null;
        }

        // Bersihkan dan trim data
        $tugas = isset($row['tugas']) ? trim($row['tugas']) : null;
        $tipe = isset($row['tipe']) ? trim($row['tipe']) : null;
        $shift = isset($row['shift']) ? trim($row['shift']) : null;
        $keterangan = isset($row['keterangan']) ? trim($row['keterangan']) : null;

        // Skip jika tugas kosong
        if (empty($tugas)) {
            return null;
        }

        // Skip jika tipe kosong
        if (empty($tipe)) {
            $this->stats['skipped']++;
            $this->stats['errors'][] = "Tugas '{$tugas}' tidak memiliki tipe";
            return null;
        }

        // Parse tanggal
        $deadline = $this->parseDate($row['deadline_date'] ?? null);

        if (empty($deadline)) {
            $this->stats['skipped']++;
            $this->stats['errors'][] = "Tugas '{$tugas}' memiliki deadline_date tidak valid: '{$row['deadline_date']}'";
            return null;
        }

        // Validasi tipe
        $tipeValid = ['Harian', 'Mingguan', 'Bulanan', 'Quartal', 'Semester', 'Tahunan'];
        if (!in_array($tipe, $tipeValid)) {
            $this->stats['skipped']++;
            $this->stats['errors'][] = "Tugas '{$tugas}' memiliki tipe tidak valid: '{$tipe}'";
            return null;
        }

        // Handle shift
        $tipeTurunan = null;
        if ($tipe === 'Harian' && !empty($shift)) {
            $shiftValid = ['Shift 1', 'Shift 2'];
            if (in_array($shift, $shiftValid)) {
                $tipeTurunan = $shift;
            }
        }

        // Cari atau buat kategori
        $kategori = KategoriDaftarTugas::firstOrCreate(
            [
                'judul_kategori' => Str::title($tugas),
                'id_user' => $this->userId,
            ],
            [
                'Tipe' => $tipe,
                'tipe_turunan' => $tipeTurunan,
                'Jabatan_Pembuat' => $this->jabatanPembuat ?? Auth::user()->jabatan,
            ],
        );

        // Cek duplikat
        $existing = KontrolTugas::where('id_karyawan', $this->userId)->where('id_DaftarTugas', $kategori->id)->whereDate('Deadline_Date', $deadline)->first();

        if ($existing) {
            $this->stats['skipped']++;
            $this->stats['errors'][] = "Tugas '{$tugas}' untuk tanggal {$deadline} sudah ada";
            return null;
        }

        $this->stats['created']++;

        return new KontrolTugas([
            'id_karyawan' => $this->userId,
            'id_DaftarTugas' => $kategori->id,
            'status' => 0,
            'Deadline_Date' => $deadline,
            'bukti' => null,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.tugas' => 'nullable|string|max:255',
            '*.tipe' => 'nullable|in:Harian,Mingguan,Bulanan,Quartal,Semester,Tahunan',
            '*.deadline_date' => 'nullable',
            '*.shift' => 'nullable|in:Shift 1,Shift 2',
            '*.keterangan' => 'nullable|string|max:500',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getStats()
    {
        return $this->stats;
    }
}
