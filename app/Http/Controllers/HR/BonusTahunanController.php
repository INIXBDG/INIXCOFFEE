<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\BonusTahunan;
use App\Models\karyawan;
use App\Models\LogGaji;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BonusTahunanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Halaman Utama Bonus Tahunan
     */
    public function index(Request $request)
    {
        $tahunSekarang = (int) $request->input('year', now()->year);
        $divisiList = karyawan::query()
            ->whereNotNull('divisi')
            ->where('divisi', '!=', '')
            ->where('divisi', '!=', 'Direksi')
            ->where('divisi', '!=', 'Pilih Divisi')
            ->distinct()
            ->orderBy('divisi')
            ->pluck('divisi');

        return view('HR.bonus_tahunan.index', compact('tahunSekarang', 'divisiList'));
    }

    /**
     * Ambil data JSON untuk tabel dan statistik
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $tahun = (int) $request->input('year', now()->year);
            $search = trim($request->input('search', ''));
            $divisi = $request->input('divisi');
            $statusKaryawan = $request->input('status_karyawan');
            $skema = $request->input('skema_bonus');

            $dataset = $this->generateBonusData($tahun, $search, $divisi, $statusKaryawan, $skema);

            return response()->json([
                'success' => true,
                'data' => $dataset['items'],
                'summary' => $dataset['summary'],
                'period' => [
                    'year' => $tahun,
                    'work_year' => $tahun - 1,
                    'month' => 12,
                    'month_name' => 'Desember',
                    'cutoff_date' => Carbon::create($tahun - 1, 12, 31)->translatedFormat('d F Y'),
                ],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Bonus Tahunan getData Error: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update skema bonus per karyawan (Standar / 2x / 4x / Custom)
     */
    public function updateSkema(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'karyawan_id' => 'required|integer|exists:karyawans,id',
            'tahun' => 'required|integer',
            'skema_bonus' => 'required|string|in:standar,2x,4x,custom',
            'basic_salary' => 'nullable|numeric|min:0',
            'custom_nominal' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string|max:500',
        ]);

        // $tahun      = tahun pencairan (filter), disimpan di kolom bonus_tahunans.tahun
        // $tahunKerja = tahun masa kerja yang dihitung (tahun pencairan - 1)
        $tahun = (int) $validated['tahun'];
        $tahunKerja = $tahun - 1;
        $emp = karyawan::findOrFail($validated['karyawan_id']);

        $joinDate = $this->determineJoinDate($emp);
        $bulanKerja = $this->calculateBulanKerjaTahunBerjalan($joinDate, $tahunKerja);
        $masaKerjaTeks = $this->hitungMasaKerjaTeks($joinDate, $tahunKerja);

        if ($request->filled('basic_salary')) {
            $basicSalary = (float) $request->input('basic_salary');
            // Sinkronkan ke master data gaji karyawan
            $tunjanganJabatan = (float) ($emp->tunjangan_jabatan ?? 0);
            $newGaji = max(0, $basicSalary - $tunjanganJabatan);
            $emp->update(['gaji' => $newGaji]);
        } else {
            // Cek apakah sebelumnya sudah tersimpan di bonus_tahunans
            $existingBonus = BonusTahunan::where('karyawan_id', $emp->id)
                ->where('tahun', $tahun)
                ->where('bulan', 12)
                ->first();

            if ($existingBonus && (float) $existingBonus->basic_salary > 0) {
                $basicSalary = (float) $existingBonus->basic_salary;
            } else {
                $basicSalary = $this->getBasicSalaryForEmployee($emp, $tahunKerja);
            }
        }

        $skema = $validated['skema_bonus'];
        $calc = $this->calculateBonusNominal(
            $bulanKerja,
            $skema,
            $basicSalary,
            (float) ($validated['custom_nominal'] ?? 0)
        );

        $bonus = BonusTahunan::updateOrCreate(
            [
                'karyawan_id' => $emp->id,
                'tahun' => $tahun,
                'bulan' => 12,
            ],
            [
                'basic_salary' => $basicSalary,
                'masa_kerja_bulan' => $bulanKerja,
                'masa_kerja_teks' => $masaKerjaTeks,
                'skema_bonus' => $skema,
                'multiplier' => $calc['multiplier'],
                'nominal_bonus' => $calc['nominal'],
                'catatan' => $validated['catatan'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Skema bonus untuk {$emp->nama_lengkap} berhasil diperbarui menjadi {$skema}.",
            'data' => $bonus,
        ]);
    }

    /**
     * Bulk update skema bonus untuk semua atau beberapa karyawan
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tahun' => 'required|integer',
            'skema_bonus' => 'required|string|in:standar,2x,4x',
            'karyawan_ids' => 'nullable|array',
            'karyawan_ids.*' => 'integer|exists:karyawans,id',
        ]);

        $tahun = (int) $validated['tahun'];
        $tahunKerja = $tahun - 1;
        $skema = $validated['skema_bonus'];

        $query = karyawan::query()
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi');

        if (!empty($validated['karyawan_ids'])) {
            $query->whereIn('id', $validated['karyawan_ids']);
        }

        $employees = $query->get();
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($employees as $emp) {
                $joinDate = $this->determineJoinDate($emp);
                $bulanKerja = $this->calculateBulanKerjaTahunBerjalan($joinDate, $tahunKerja);
                $masaKerjaTeks = $this->hitungMasaKerjaTeks($joinDate, $tahunKerja);

                $basicSalary = $this->getBasicSalaryForEmployee($emp, $tahunKerja);
                $calc = $this->calculateBonusNominal($bulanKerja, $skema, $basicSalary);

                BonusTahunan::updateOrCreate(
                    [
                        'karyawan_id' => $emp->id,
                        'tahun' => $tahun,
                        'bulan' => 12,
                    ],
                    [
                        'basic_salary' => $basicSalary,
                        'masa_kerja_bulan' => $bulanKerja,
                        'masa_kerja_teks' => $masaKerjaTeks,
                        'skema_bonus' => $skema,
                        'multiplier' => $calc['multiplier'],
                        'nominal_bonus' => $calc['nominal'],
                    ]
                );
                $updatedCount++;
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil memperbarui {$updatedCount} karyawan ke skema {$skema}.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal bulk update: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hitung ulang semua data untuk tahun tertentu
     */
    public function recalculateAll(Request $request): JsonResponse
    {
        $tahun = (int) $request->input('year', now()->year);
        $dataset = $this->generateBonusData($tahun);

        // Simpan / refresh semua data ke database
        foreach ($dataset['items'] as $item) {
            BonusTahunan::updateOrCreate(
                [
                    'karyawan_id' => $item['karyawan_id'],
                    'tahun' => $tahun,
                    'bulan' => 12,
                ],
                [
                    'basic_salary' => $item['basic_salary'],
                    'masa_kerja_bulan' => $item['masa_kerja_bulan'],
                    'masa_kerja_teks' => $item['masa_kerja'],
                    'skema_bonus' => $item['skema_bonus'],
                    'multiplier' => $item['multiplier'],
                    'nominal_bonus' => $item['nominal_bonus'],
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Data bonus tahunan untuk tahun {$tahun} berhasil dihitung ulang dan disimpan.",
        ]);
    }

    /**
     * Export ke Excel
     */
    public function exportExcel(Request $request)
    {
        $tahun = (int) $request->input('year', now()->year);
        $search = trim($request->input('search', ''));
        $divisi = $request->input('divisi');
        $statusKaryawan = $request->input('status_karyawan');
        $skema = $request->input('skema_bonus');

        $dataset = $this->generateBonusData($tahun, $search, $divisi, $statusKaryawan, $skema);
        $items = $dataset['items'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Bonus Tahunan {$tahun}");

        // Styles
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];

        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];

        $currencyStyle = [
            'numberFormat' => ['formatCode' => '#,##0'],
        ];

        $totalStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7E6E6'],
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']]],
        ];

        // Title
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', "DAFTAR PENCAIRAN BONUS TAHUNAN - DESEMBER {$tahun}");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $tahunKerja = $tahun - 1;
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', "INIXINDO • Periode Pencairan: Desember {$tahun} • Masa Kerja Tahun {$tahunKerja}");
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // Header Table
        $headers = [
            'No',
            'Jabatan',
            'Nama Lengkap',
            'Basic Salary (Rp)',
            'Rekening Maybank',
            'Status Karyawan',
            'Masa Kerja',
            'Skema Bonus',
            'Nominal Bonus (Rp)',
        ];
        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle('A4:I4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(26);

        $rowNum = 5;
        $i = 1;
        $totalBasic = 0;
        $totalBonus = 0;

        foreach ($items as $row) {
            $sheet->setCellValue("A{$rowNum}", $i++);
            $sheet->setCellValue("B{$rowNum}", $row['jabatan']);
            $sheet->setCellValue("C{$rowNum}", $row['nama_lengkap']);
            $sheet->setCellValue("D{$rowNum}", $row['basic_salary']);
            $sheet->setCellValueExplicit("E{$rowNum}", (string) $row['rekening_maybank'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("F{$rowNum}", $row['status_karyawan']);
            $sheet->setCellValue("G{$rowNum}", $row['masa_kerja']);
            $sheet->setCellValue("H{$rowNum}", strtoupper($row['skema_bonus']));
            $sheet->setCellValue("I{$rowNum}", $row['nominal_bonus']);

            $totalBasic += (float) $row['basic_salary'];
            $totalBonus += (float) $row['nominal_bonus'];

            $rowNum++;
        }

        $lastRow = $rowNum - 1;
        if ($lastRow >= 5) {
            $sheet->getStyle("A5:I{$lastRow}")->applyFromArray($dataStyle);
            $sheet->getStyle("D5:D{$lastRow}")->applyFromArray($currencyStyle);
            $sheet->getStyle("I5:I{$lastRow}")->applyFromArray($currencyStyle);

            $sheet->getStyle("A5:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E5:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Row Total
        $sheet->mergeCells("A{$rowNum}:C{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", 'TOTAL');
        $sheet->setCellValue("D{$rowNum}", $totalBasic);
        $sheet->setCellValue("I{$rowNum}", $totalBonus);
        $sheet->getStyle("A{$rowNum}:I{$rowNum}")->applyFromArray($totalStyle);
        $sheet->getStyle("D{$rowNum}")->applyFromArray($currencyStyle);
        $sheet->getStyle("I{$rowNum}")->applyFromArray($currencyStyle);
        $sheet->getStyle("A{$rowNum}:C{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "Bonus_Tahunan_Desember_{$tahun}.xlsx";
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer->save('php://output');
        exit();
    }

    /**
     * Export ke PDF
     */
    public function exportPdf(Request $request)
    {
        $tahun = (int) $request->input('year', now()->year);
        $search = trim($request->input('search', ''));
        $divisi = $request->input('divisi');
        $statusKaryawan = $request->input('status_karyawan');
        $skema = $request->input('skema_bonus');

        $dataset = $this->generateBonusData($tahun, $search, $divisi, $statusKaryawan, $skema);
        $items = $dataset['items'];
        $summary = $dataset['summary'];

        $pdf = Pdf::loadView('HR.bonus_tahunan.pdf', compact('items', 'summary', 'tahun'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("Bonus_Tahunan_Desember_{$tahun}.pdf");
    }

    /**
     * Helper: Menghasilkan dataset bonus tahunan secara terpadu.
     *
     * $tahun      = tahun pencairan (filter), contoh 2026
     * $tahunKerja = tahun masa kerja yang dihitung (tahun - 1), contoh 2025
     */
    private function generateBonusData(int $tahun, string $search = '', ?string $divisi = null, ?string $statusFilter = null, ?string $skemaFilter = null): array
    {
        $tahunKerja = $tahun - 1;
        $cutoff = Carbon::create($tahunKerja, 12, 31)->endOfDay();
        $startOfYear = Carbon::create($tahunKerja, 1, 1)->startOfDay();

        $baseQuery = karyawan::query()
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi')
            ->where('divisi', '!=', 'Pilih Divisi')
            ->where(function ($q) use ($startOfYear) {
                // Aktif atau resign setelah awal tahun masa kerja
                $q->whereNull('resigned_at')
                  ->orWhere('resigned_at', '>=', $startOfYear);
            });

        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%")
                  ->orWhere('jabatan', 'LIKE', "%{$search}%")
                  ->orWhere('rekening_maybank', 'LIKE', "%{$search}%");
            });
        }

        if ($divisi && $divisi !== 'all' && $divisi !== 'Pilih Divisi') {
            $baseQuery->where('divisi', $divisi);
        }

        $employees = $baseQuery->orderBy('divisi')->orderBy('nama_lengkap')->get();

        // Ambil data tersimpan di bonus_tahunans (periode pencairan = $tahun)
        $savedRecords = BonusTahunan::where('tahun', $tahun)
            ->where('bulan', 12)
            ->get()
            ->keyBy('karyawan_id');

        $items = collect();

        foreach ($employees as $emp) {
            $joinDate = $this->determineJoinDate($emp);
            $bulanKerja = $this->calculateBulanKerjaTahunBerjalan($joinDate, $tahunKerja);

            $totalBulan = $joinDate ? max(0, (int) $joinDate->diffInMonths($cutoff)) : 0;
            $masaKerjaTeks = $this->formatMasaKerja($totalBulan);

            $statusStr = $this->determineStatusKaryawan($emp, $cutoff);

            // Cek apakah ada filter status
            if ($statusFilter && $statusFilter !== 'all' && $statusStr !== $statusFilter) {
                continue;
            }

            $saved = $savedRecords->get($emp->id);

            // Basic salary
            $basicSalary = ($saved && (float) $saved->basic_salary > 0)
                ? (float) $saved->basic_salary
                : $this->getBasicSalaryForEmployee($emp, $tahunKerja);

            // Skema & Multiplier & Nominal
            if ($saved) {
                $skema = $saved->skema_bonus;
                $catatan = $saved->catatan;

                if ($skema === 'custom') {
                    $nominal = (float) $saved->nominal_bonus;
                    $multiplier = (float) $saved->multiplier;
                } else {
                    // Selalu hitung dengan rumus: (Basic Salary ÷ 12) × bulanKerja
                    $calc = $this->calculateBonusNominal($bulanKerja, $skema, $basicSalary);
                    $multiplier = $calc['multiplier'];
                    $nominal = $calc['nominal'];
                }
            } else {
                // Standar default kalkulasi (Prorata)
                $skema = 'standar';
                $calc = $this->calculateBonusNominal($bulanKerja, $skema, $basicSalary);
                $multiplier = $calc['multiplier'];
                $nominal = $calc['nominal'];
                $catatan = null;
            }

            // Cek filter skema
            if ($skemaFilter && $skemaFilter !== 'all' && $skema !== $skemaFilter) {
                continue;
            }

            $items->push([
                'id' => $saved ? $saved->id : null,
                'karyawan_id' => $emp->id,
                'nip' => $emp->nip ?? '-',
                'nama_lengkap' => $emp->nama_lengkap ?? '-',
                'divisi' => $emp->divisi ?? '-',
                'jabatan' => $emp->jabatan ?? '-',
                'basic_salary' => $basicSalary,
                'rekening_maybank' => $emp->rekening_maybank ?: '-',
                'status_karyawan' => $statusStr,
                'tanggal_bergabung' => $joinDate ? $joinDate->translatedFormat('d M Y') : '-',
                'masa_kerja' => $masaKerjaTeks,
                'masa_kerja_bulan' => $bulanKerja,
                'bulan_kerja' => $bulanKerja,
                'skema_bonus' => $skema,
                'multiplier' => $multiplier,
                'nominal_bonus' => $nominal,
                'catatan' => $catatan,
                'is_saved' => (bool) $saved,
            ]);
        }

        $summary = [
            'total_karyawan' => $items->count(),
            'total_basic_salary' => $items->sum('basic_salary'),
            'total_nominal_bonus' => $items->sum('nominal_bonus'),
            'count_standar' => $items->where('skema_bonus', 'standar')->count(),
            'count_2x' => $items->where('skema_bonus', '2x')->count(),
            'count_4x' => $items->where('skema_bonus', '4x')->count(),
            'count_custom' => $items->where('skema_bonus', 'custom')->count(),
        ];

        return [
            'items' => $items->values(),
            'summary' => $summary,
        ];
    }

    /**
     * Hitung berapa bulan karyawan bekerja pada $tahunKerja (Januari - Desember):
     * - Masuk di tahun yang sama dengan $tahunKerja:
     *     Masa Kerja = 12 - Bulan Masuk + 1
     *     (Contoh: Masuk 1 April 2025 -> 12 - 4 + 1 = 9 bulan)
     * - Masuk di tahun sebelumnya: 12 bulan (full year)
     * - Masuk setelah $tahunKerja: 0 bulan
     */
    private function calculateBulanKerjaTahunBerjalan(?Carbon $joinDate, int $tahunKerja): int
    {
        if (!$joinDate) {
            return 0;
        }

        $joinYear = $joinDate->year;
        if ($joinYear < $tahunKerja) {
            return 12;
        } elseif ($joinYear === $tahunKerja) {
            return max(0, min(12, 12 - $joinDate->month + 1));
        }

        return 0;
    }

    /**
     * Hitung nominal dan multiplier bonus tahunan:
     * Rumus: (Basic Salary ÷ 12) × Masa Kerja Bulan di tahun masa kerja
     * Skema:
     * - Standar (Prorata): 1 × (Basic Salary ÷ 12) × Masa Kerja
     * - 2x: 2 × (Basic Salary ÷ 12) × Masa Kerja
     * - 4x: 4 × (Basic Salary ÷ 12) × Masa Kerja
     * - Custom: custom_nominal
     */
    private function calculateBonusNominal(int $bulanKerja, string $skema, float $basicSalary, float $customNominal = 0): array
    {
        if ($skema === 'custom') {
            $nominal = $customNominal;
            $multiplier = $basicSalary > 0 ? round($nominal / $basicSalary, 4) : 1.0;
            return [
                'multiplier' => $multiplier,
                'nominal' => $nominal,
            ];
        }

        $prorataMultiplier = $bulanKerja / 12;

        if ($skema === '2x') {
            $multiplier = round(2.00 * $prorataMultiplier, 4);
            $nominal = round(2.00 * ($basicSalary / 12) * $bulanKerja, 0);
        } elseif ($skema === '4x') {
            $multiplier = round(4.00 * $prorataMultiplier, 4);
            $nominal = round(4.00 * ($basicSalary / 12) * $bulanKerja, 0);
        } else {
            // standar (prorata)
            $multiplier = round($prorataMultiplier, 4);
            $nominal = round(($basicSalary / 12) * $bulanKerja, 0);
        }

        return [
            'multiplier' => $multiplier,
            'nominal' => $nominal,
        ];
    }

    /**
     * Helper: Menentukan tanggal bergabung karyawan
     */
    private function determineJoinDate($emp): ?Carbon
    {
        $raw = $emp->awal_probation ?? $emp->awal_kontrak ?? $emp->awal_tetap ?? $emp->created_at;
        return $raw ? Carbon::parse($raw)->startOfDay() : null;
    }

    /**
     * Helper: Teks masa kerja sampai cut-off 31 Desember $tahunKerja
     */
    private function hitungMasaKerjaTeks(?Carbon $joinDate, int $tahunKerja): string
    {
        $cutoff = Carbon::create($tahunKerja, 12, 31)->endOfDay();
        $totalBulan = $joinDate ? max(0, (int) $joinDate->diffInMonths($cutoff)) : 0;

        return $this->formatMasaKerja($totalBulan);
    }

    /**
     * Helper: Format masa kerja ke "X Thn Y Bln"
     */
    private function formatMasaKerja(int $totalBulan): string
    {
        if ($totalBulan <= 0) {
            return '< 1 Bln';
        }
        $thn = (int) floor($totalBulan / 12);
        $bln = $totalBulan % 12;

        if ($thn > 0 && $bln > 0) {
            return "{$thn} Thn {$bln} Bln";
        } elseif ($thn > 0) {
            return "{$thn} Tahun";
        }

        return "{$bln} Bulan";
    }

    /**
     * Helper: Menentukan status kepegawaian pada tanggal cut-off
     */
    private function determineStatusKaryawan($emp, Carbon $cutoff): string
    {
        if (!empty($emp->resigned_at) && Carbon::parse($emp->resigned_at)->lte($cutoff)) {
            return 'Resign';
        }

        if (!empty($emp->awal_tetap) && Carbon::parse($emp->awal_tetap)->lte($cutoff)) {
            return 'Karyawan Tetap';
        }

        if (!empty($emp->awal_kontrak) && Carbon::parse($emp->awal_kontrak)->lte($cutoff)) {
            return 'Karyawan Kontrak';
        }

        if (!empty($emp->awal_probation) && Carbon::parse($emp->awal_probation)->lte($cutoff)) {
            return 'Karyawan Probation';
        }

        return 'Karyawan Tetap';
    }

    /**
     * Helper: Mengambil Basic Salary (Gaji Pokok + Tunjangan Jabatan)
     * berdasarkan log gaji terakhir sampai dengan $tahunKerja.
     */
    private function getBasicSalaryForEmployee($emp, int $tahunKerja): float
    {
        $log = LogGaji::where('id_karyawan', $emp->id)
            ->where('tahun', '<=', $tahunKerja)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->first();

        if ($log && ((float) ($log->gaji ?? 0) > 0)) {
            return (float) ($log->gaji ?? 0) + (float) ($log->tunjangan_jabatan ?? 0);
        }

        // Fallback dari master karyawan
        $gaji = (float) ($emp->gaji ?? 0);
        $tunjanganJabatan = (float) ($emp->tunjangan_jabatan ?? 0);

        return $gaji + $tunjanganJabatan;
    }
}