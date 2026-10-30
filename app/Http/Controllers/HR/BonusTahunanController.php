<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\BonusTahunan;
use App\Models\karyawan;
use App\Models\LogGaji;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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

    private function ensureTableExists(): void
    {
        if (!Schema::hasTable('bonus_tahunans')) {
            Schema::create('bonus_tahunans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
                $table->integer('tahun')->index();
                $table->integer('bulan')->default(12)->index();
                $table->string('jabatan')->nullable();
                $table->decimal('basic_salary', 15, 2)->default(0);
                $table->string('rekening_maybank')->nullable();
                $table->string('status_karyawan')->nullable();
                $table->date('tanggal_bergabung')->nullable();
                $table->integer('masa_kerja_bulan')->default(0);
                $table->string('masa_kerja_teks')->nullable();
                $table->string('skema_bonus', 30)->default('standar');
                $table->decimal('multiplier', 8, 2)->default(1.00);
                $table->decimal('nominal_bonus', 15, 2)->default(0);
                $table->text('catatan')->nullable();
                $table->boolean('is_locked')->default(false);
                $table->timestamps();

                $table->unique(['karyawan_id', 'tahun', 'bulan'], 'unique_bonus_karyawan_periode');
            });
        }
    }

    public function index(Request $request)
    {
        $this->ensureTableExists();

        $tahunSekarang = (int) $request->input('year', now()->year);
        $divisiList = karyawan::query()
            ->whereNotNull('divisi')
            ->where('divisi', '!=', '')
            ->where('divisi', '!=', 'Pilih Divisi')
            ->distinct()
            ->orderBy('divisi')
            ->pluck('divisi');

        return view('HR.bonus_tahunan.index', compact('tahunSekarang', 'divisiList'));
    }

    public function getData(Request $request): JsonResponse
    {
        $this->ensureTableExists();

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
                    'year_bonus' => $tahun - 1,
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

    public function updateSkema(Request $request): JsonResponse
    {
        $this->ensureTableExists();

        $validated = $request->validate([
            'karyawan_id' => 'required|integer|exists:karyawans,id',
            'tahun' => 'required|integer',
            'skema_bonus' => 'required|string|in:standar,2x,4x,custom',
            'basic_salary' => 'nullable|numeric|min:0',
            'custom_nominal' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string|max:500',
        ]);

        $tahun = (int) $validated['tahun'];
        $emp = karyawan::findOrFail($validated['karyawan_id']);
        $masa = $this->hitungMasaKerja($emp, $tahun);

        if (!$masa['berhak']) {
            return response()->json([
                'success' => false,
                'message' => "{$emp->nama_lengkap} belum berhak menerima bonus pencairan Desember {$tahun} karena tidak bekerja pada tahun " . ($tahun - 1) . '.',
            ], 422);
        }

        if ($request->filled('basic_salary')) {
            $basicSalary = (float) $request->input('basic_salary');
            $tunjanganJabatan = (float) ($emp->tunjangan_jabatan ?? 0);
            $newGaji = max(0, $basicSalary - $tunjanganJabatan);
            $emp->update(['gaji' => $newGaji]);
        } else {
            $existingBonus = BonusTahunan::where('karyawan_id', $emp->id)->where('tahun', $tahun)->where('bulan', 12)->first();
            if ($existingBonus && (float) $existingBonus->basic_salary > 0) {
                $basicSalary = (float) $existingBonus->basic_salary;
            } else {
                $basicSalary = $this->getBasicSalaryForEmployee($emp, $tahun);
            }
        }

        $skema = $validated['skema_bonus'];
        $calc = $this->calculateBonusNominal(
            $masa['bulan_kerja'],
            $skema,
            $basicSalary,
            (float) ($validated['custom_nominal'] ?? 0)
        );

        $statusStr = $this->determineStatusKaryawan($emp, $masa['cutoff']);

        $bonus = BonusTahunan::updateOrCreate(
            [
                'karyawan_id' => $emp->id,
                'tahun' => $tahun,
                'bulan' => 12,
            ],
            [
                'jabatan' => $emp->jabatan,
                'basic_salary' => $basicSalary,
                'rekening_maybank' => $emp->rekening_maybank,
                'status_karyawan' => $statusStr,
                'tanggal_bergabung' => $masa['join_date']?->toDateString(),
                'masa_kerja_bulan' => $masa['bulan_kerja'],
                'masa_kerja_teks' => $masa['teks'],
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

    public function bulkUpdate(Request $request): JsonResponse
    {
        $this->ensureTableExists();

        $validated = $request->validate([
            'tahun' => 'required|integer',
            'skema_bonus' => 'required|string|in:standar,2x,4x',
            'karyawan_ids' => 'nullable|array',
            'karyawan_ids.*' => 'integer|exists:karyawans,id',
        ]);

        $tahun = (int) $validated['tahun'];
        $skema = $validated['skema_bonus'];

        $query = $this->baseEmployeeQuery();

        if (!empty($validated['karyawan_ids'])) {
            $query->whereIn('id', $validated['karyawan_ids']);
        }

        $employees = $query->get();
        $updatedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($employees as $emp) {
                $masa = $this->hitungMasaKerja($emp, $tahun);

                if (!$masa['berhak']) {
                    $skippedCount++;
                    continue;
                }

                $basicSalary = $this->getBasicSalaryForEmployee($emp, $tahun);
                $statusStr = $this->determineStatusKaryawan($emp, $masa['cutoff']);
                $calc = $this->calculateBonusNominal($masa['bulan_kerja'], $skema, $basicSalary);

                BonusTahunan::updateOrCreate(
                    [
                        'karyawan_id' => $emp->id,
                        'tahun' => $tahun,
                        'bulan' => 12,
                    ],
                    [
                        'jabatan' => $emp->jabatan,
                        'basic_salary' => $basicSalary,
                        'rekening_maybank' => $emp->rekening_maybank,
                        'status_karyawan' => $statusStr,
                        'tanggal_bergabung' => $masa['join_date']?->toDateString(),
                        'masa_kerja_bulan' => $masa['bulan_kerja'],
                        'masa_kerja_teks' => $masa['teks'],
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
                'message' => "Berhasil memperbarui {$updatedCount} karyawan ke skema {$skema}. {$skippedCount} karyawan dilewati karena belum berhak.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal bulk update: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function recalculateAll(Request $request): JsonResponse
    {
        $this->ensureTableExists();

        $tahun = (int) $request->input('year', now()->year);
        $dataset = $this->generateBonusData($tahun);

        DB::beginTransaction();
        try {
            foreach ($dataset['items'] as $item) {
                if (!$item['berhak']) {
                    continue;
                }

                BonusTahunan::updateOrCreate(
                    [
                        'karyawan_id' => $item['karyawan_id'],
                        'tahun' => $tahun,
                        'bulan' => 12,
                    ],
                    [
                        'jabatan' => $item['jabatan'],
                        'basic_salary' => $item['basic_salary'],
                        'rekening_maybank' => $item['rekening_maybank'] === '-' ? null : $item['rekening_maybank'],
                        'status_karyawan' => $item['status_karyawan'],
                        'tanggal_bergabung' => $item['tanggal_bergabung_raw'],
                        'masa_kerja_bulan' => $item['masa_kerja_bulan'],
                        'masa_kerja_teks' => $item['masa_kerja'],
                        'skema_bonus' => $item['skema_bonus'],
                        'multiplier' => $item['multiplier'],
                        'nominal_bonus' => $item['nominal_bonus'],
                    ]
                );
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung ulang: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Data bonus tahunan pencairan Desember {$tahun} berhasil dihitung ulang dan disimpan.",
        ]);
    }

    public function exportExcel(Request $request)
    {
        $this->ensureTableExists();

        $tahun = (int) $request->input('year', now()->year);
        $tahunBonus = $tahun - 1;
        $search = trim($request->input('search', ''));
        $divisi = $request->input('divisi');
        $statusKaryawan = $request->input('status_karyawan');
        $skema = $request->input('skema_bonus');

        $dataset = $this->generateBonusData($tahun, $search, $divisi, $statusKaryawan, $skema);
        $items = $dataset['items']->where('berhak', true)->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Bonus Tahunan {$tahun}");

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

        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', "DAFTAR PENCAIRAN BONUS TAHUNAN - DESEMBER {$tahun}");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', "PT INIXINDO AMIETE MANDIRI • Periode Kerja: Januari - Desember {$tahunBonus} • Pencairan: Desember {$tahun}");
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        $sheet->getRowDimension(2)->setRowHeight(18);

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

    public function exportPdf(Request $request)
    {
        $this->ensureTableExists();

        $tahun = (int) $request->input('year', now()->year);
        $tahunBonus = $tahun - 1;
        $mode = $request->input('mode', 'rekap');
        if (!in_array($mode, ['rekap', 'per_orang', 'semua_surat'], true)) {
            $mode = 'rekap';
        }
        $polos = filter_var($request->input('polos', false), FILTER_VALIDATE_BOOLEAN);
        $nomorSurat = trim((string) $request->input('nomor_surat', ''));
        if ($nomorSurat === '') {
            $nomorSurat = '........../HRD-INIX/XII/' . $tahun;
        }

        if ($mode === 'per_orang') {
            $karyawanId = (int) $request->input('karyawan_id', 0);
            $dataset = $this->generateBonusData($tahun);
            $items = $dataset['items']
                ->where('berhak', true)
                ->where('karyawan_id', $karyawanId)
                ->values();

            if ($items->isEmpty()) {
                abort(404, 'Data karyawan tidak ditemukan atau belum berhak menerima bonus.');
            }
        } else {
            $search = trim($request->input('search', ''));
            $divisi = $request->input('divisi');
            $statusKaryawan = $request->input('status_karyawan');
            $skema = $request->input('skema_bonus');

            $dataset = $this->generateBonusData($tahun, $search, $divisi, $statusKaryawan, $skema);
            $items = $dataset['items']->where('berhak', true)->values();
        }

        $items = $items->map(fn ($row) => $this->decorateItem($row, $tahunBonus))->values();

        $totalBonus = (float) $items->sum('nominal_bonus');
        $summary = [
            'total_karyawan' => $items->count(),
            'total_basic_salary' => (float) $items->sum('basic_salary'),
            'total_nominal_bonus' => $totalBonus,
            'total_terbilang' => $this->terbilangRupiah($totalBonus),
        ];

        $data = [
            'items' => $items,
            'summary' => $summary,
            'tahun' => $tahun,
            'tahunBonus' => $tahunBonus,
            'polos' => $polos,
            'nomorSurat' => $nomorSurat,
            'profil' => $this->companyProfile(),
            'ttd' => $this->resolveTandaTangan(),
            'logo' => $polos ? null : $this->assetDataUri('assets/img/inixs'),
            'background' => $polos ? null : $this->assetDataUri('assets/surat/backgrounds/kops'),
        ];

        $view = $mode === 'rekap' ? 'HR.bonus_tahunan.pdf_rekap' : 'HR.bonus_tahunan.pdf_surat';

        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');

        $suffix = $polos ? '_Polos' : '';

        if ($mode === 'per_orang') {
            $nama = Str::slug($items->first()['nama_lengkap'], '_');
            return $pdf->download("Surat_Bonus_{$nama}_Desember_{$tahun}{$suffix}.pdf");
        }

        if ($mode === 'semua_surat') {
            return $pdf->download("Surat_Bonus_Per_Karyawan_Desember_{$tahun}{$suffix}.pdf");
        }

        return $pdf->download("Surat_Rekap_Bonus_Tahunan_Desember_{$tahun}{$suffix}.pdf");
    }

    private function resolveTandaTangan(): array
    {
        $user = Auth::user();

        $namaPembuat = '';
        $jabatanPembuat = '';

        if ($user) {
            $namaPembuat = (string) ($user->nama_lengkap ?? $user->name ?? $user->nama ?? $user->username ?? $user->email ?? '');

            $karyawanUser = $user->karyawan ?? null;
            if (!$karyawanUser instanceof karyawan && $namaPembuat !== '') {
                $karyawanUser = karyawan::whereRaw('LOWER(TRIM(nama_lengkap)) = ?', [mb_strtolower(trim($namaPembuat))])->first();
            }

            if ($karyawanUser instanceof karyawan) {
                $namaPembuat = $karyawanUser->nama_lengkap ?: $namaPembuat;
                $jabatanPembuat = (string) ($karyawanUser->jabatan ?? '');
            }
        }

        $gm = $this->findKaryawanByJabatan(['gm', 'general manager']);
        $dirut = $this->findKaryawanByJabatan(['direktur utama']);

        return [
            [
                'peran' => 'Dibuat oleh,',
                'nama' => $namaPembuat,
                'jabatan' => $jabatanPembuat,
            ],
            [
                'peran' => 'Disetujui oleh,',
                'nama' => $gm?->nama_lengkap ?? '',
                'jabatan' => $gm?->jabatan ?? 'GM',
            ],
            [
                'peran' => 'Disetujui oleh,',
                'nama' => $dirut?->nama_lengkap ?? '',
                'jabatan' => $dirut?->jabatan ?? 'Direktur Utama',
            ],
        ];
    }

    private function findKaryawanByJabatan(array $jabatanList): ?karyawan
    {
        $placeholders = implode(',', array_fill(0, count($jabatanList), '?'));

        return karyawan::query()
            ->whereRaw("LOWER(TRIM(jabatan)) IN ({$placeholders})", $jabatanList)
            ->where(function ($q) {
                $q->whereNull('resigned_at')
                  ->orWhere('resigned_at', '!=', '1');
            })
            ->orderBy('id')
            ->first();
    }

    private function companyProfile(): array
    {
        return [
            'nama' => 'INIXINDO',
            'unit' => 'Human Resources Department',
            'alamat' => 'Alamat lengkap perusahaan',
            'kontak' => 'Telp. (000) 0000000  |  Email: hrd@perusahaan.co.id',
            'kota' => '.....................',
        ];
    }

    private function assetDataUri(string $basePath): ?string
    {
        $basePath = preg_replace('/\.(png|jpe?g)$/i', '', $basePath);

        foreach (['png', 'jpg', 'jpeg'] as $ext) {
            $path = public_path("{$basePath}.{$ext}");
            if (is_file($path)) {
                $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        return null;
    }

    private function decorateItem(array $row, int $tahunBonus): array
    {
        $skema = $row['skema_bonus'];
        $labels = [
            'standar' => 'Standar (Prorata)',
            '2x' => '2x Basic Salary',
            '4x' => '4x Basic Salary',
            'custom' => 'Custom (Nominal Khusus)',
        ];
        $pengali = ['standar' => 1, '2x' => 2, '4x' => 4][$skema] ?? null;
        $basicFmt = 'Rp ' . number_format((float) $row['basic_salary'], 0, ',', '.');

        if ($skema === 'custom' || $pengali === null) {
            $rumus = 'Nominal ditetapkan secara khusus';
        } else {
            $rumus = ($pengali > 1 ? "{$pengali} × " : '') . "({$basicFmt} ÷ 12) × {$row['bulan_kerja']} bulan";
        }

        $bulanMulai = 'Januari';
        if (empty($row['is_direksi']) && !empty($row['tanggal_bergabung_raw'])) {
            $join = Carbon::parse($row['tanggal_bergabung_raw']);
            if ($join->year === $tahunBonus) {
                $bulanMulai = $join->translatedFormat('F');
            }
        }

        return array_merge($row, [
            'skema_label' => $labels[$skema] ?? strtoupper($skema),
            'rumus' => $rumus,
            'periode_teks' => "{$bulanMulai} - Desember {$tahunBonus}",
            'terbilang' => $this->terbilangRupiah((float) $row['nominal_bonus']),
        ]);
    }

    private function terbilangRupiah(float $angka): string
    {
        $angka = (int) round(abs($angka));
        if ($angka === 0) {
            return 'Nol rupiah';
        }

        return ucfirst(trim($this->terbilang($angka))) . ' rupiah';
    }

    private function terbilang(int $angka): string
    {
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($angka < 12) {
            return $huruf[$angka];
        }
        if ($angka < 20) {
            return $this->terbilang($angka - 10) . ' belas';
        }
        if ($angka < 100) {
            return $this->terbilang(intdiv($angka, 10)) . ' puluh' . ($angka % 10 ? ' ' . $this->terbilang($angka % 10) : '');
        }
        if ($angka < 200) {
            return 'seratus' . ($angka - 100 ? ' ' . $this->terbilang($angka - 100) : '');
        }
        if ($angka < 1000) {
            return $this->terbilang(intdiv($angka, 100)) . ' ratus' . ($angka % 100 ? ' ' . $this->terbilang($angka % 100) : '');
        }
        if ($angka < 2000) {
            return 'seribu' . ($angka - 1000 ? ' ' . $this->terbilang($angka - 1000) : '');
        }
        if ($angka < 1000000) {
            return $this->terbilang(intdiv($angka, 1000)) . ' ribu' . ($angka % 1000 ? ' ' . $this->terbilang($angka % 1000) : '');
        }
        if ($angka < 1000000000) {
            return $this->terbilang(intdiv($angka, 1000000)) . ' juta' . ($angka % 1000000 ? ' ' . $this->terbilang($angka % 1000000) : '');
        }
        if ($angka < 1000000000000) {
            return $this->terbilang(intdiv($angka, 1000000000)) . ' miliar' . ($angka % 1000000000 ? ' ' . $this->terbilang($angka % 1000000000) : '');
        }

        return $this->terbilang(intdiv($angka, 1000000000000)) . ' triliun' . ($angka % 1000000000000 ? ' ' . $this->terbilang($angka % 1000000000000) : '');
    }

    private function baseEmployeeQuery()
    {
        return karyawan::query()
            ->where(function ($q) {
                $q->whereNull('resigned_at')
                  ->orWhere('resigned_at', '!=', '1');
            })
            ->whereNotNull('nip')
            ->where('nip', '!=', '')
            ->where(function ($q) {
                $q->whereNull('jabatan')
                  ->orWhere('jabatan', 'NOT LIKE', '%OL%');
            })
            ->whereNot('jabatan', 'Outsource')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->where('divisi', '!=', 'Pilih Divisi');
    }

    private function generateBonusData(int $tahun, string $search = '', ?string $divisi = null, ?string $statusFilter = null, ?string $skemaFilter = null): array
    {
        $baseQuery = $this->baseEmployeeQuery();

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

        $savedRecords = BonusTahunan::where('tahun', $tahun)
            ->where('bulan', 12)
            ->get()
            ->keyBy('karyawan_id');

        $items = collect();

        foreach ($employees as $emp) {
            $masa = $this->hitungMasaKerja($emp, $tahun);
            $statusStr = $this->determineStatusKaryawan($emp, $masa['cutoff']);

            if ($statusFilter && $statusFilter !== 'all' && $statusStr !== $statusFilter) {
                continue;
            }

            $saved = $savedRecords->get($emp->id);

            $basicSalary = ($saved && (float) $saved->basic_salary > 0)
                ? (float) $saved->basic_salary
                : $this->getBasicSalaryForEmployee($emp, $tahun);

            $catatan = null;

            if (!$masa['berhak']) {
                $skema = 'standar';
                $multiplier = 0;
                $nominal = 0;
            } elseif ($saved) {
                $skema = $saved->skema_bonus;
                $catatan = $saved->catatan;

                if ($skema === 'custom') {
                    $nominal = (float) $saved->nominal_bonus;
                    $multiplier = (float) $saved->multiplier;
                } else {
                    $calc = $this->calculateBonusNominal($masa['bulan_kerja'], $skema, $basicSalary);
                    $multiplier = $calc['multiplier'];
                    $nominal = $calc['nominal'];
                }
            } else {
                $skema = 'standar';
                $calc = $this->calculateBonusNominal($masa['bulan_kerja'], $skema, $basicSalary);
                $multiplier = $calc['multiplier'];
                $nominal = $calc['nominal'];
            }

            if ($skemaFilter && $skemaFilter !== 'all' && $skema !== $skemaFilter) {
                continue;
            }

            $joinDate = $masa['join_date'];

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
                'tanggal_bergabung_raw' => $joinDate?->toDateString(),
                'masa_kerja' => $masa['teks'],
                'masa_kerja_bulan' => $masa['bulan_kerja'],
                'bulan_kerja' => $masa['bulan_kerja'],
                'skema_bonus' => $skema,
                'multiplier' => $multiplier,
                'nominal_bonus' => $nominal,
                'catatan' => $catatan,
                'is_saved' => (bool) $saved,
                'is_direksi' => $masa['is_direksi'],
                'berhak' => $masa['berhak'],
                'keterangan' => $masa['keterangan'],
            ]);
        }

        $eligible = $items->where('berhak', true);

        $summary = [
            'total_karyawan' => $eligible->count(),
            'total_tidak_berhak' => $items->where('berhak', false)->count(),
            'total_basic_salary' => $eligible->sum('basic_salary'),
            'total_nominal_bonus' => $eligible->sum('nominal_bonus'),
            'count_standar' => $eligible->where('skema_bonus', 'standar')->count(),
            'count_2x' => $eligible->where('skema_bonus', '2x')->count(),
            'count_4x' => $eligible->where('skema_bonus', '4x')->count(),
            'count_custom' => $eligible->where('skema_bonus', 'custom')->count(),
        ];

        return [
            'items' => $items->values(),
            'summary' => $summary,
        ];
    }

    private function hitungMasaKerja($emp, int $tahun): array
    {
        $tahunBonus = $tahun - 1;
        $cutoff = Carbon::create($tahunBonus, 12, 31)->endOfDay();
        $joinDate = $this->determineJoinDate($emp);
        $isDireksi = $this->isDireksi($emp);

        $bulanKerja = $this->calculateBulanKerjaTahunBonus($joinDate, $tahunBonus, $isDireksi);
        $berhak = $bulanKerja > 0;

        if ($isDireksi) {
            $teks = '12 Bulan';
            $keterangan = 'Direksi - masa kerja default 12 bulan';
        } elseif (!$berhak) {
            $teks = '-';
            $keterangan = "Masuk setelah {$tahunBonus} - belum berhak bonus";
        } else {
            $totalBulan = ($joinDate && $joinDate->lte($cutoff)) ? (int) $joinDate->diffInMonths($cutoff) : 0;
            $teks = $this->formatMasaKerja($totalBulan);
            $keterangan = $joinDate && $joinDate->year === $tahunBonus
                ? "Dihitung {$bulanKerja} bulan (awal kerja s/d Des {$tahunBonus})"
                : "Full 12 bulan tahun {$tahunBonus}";
        }

        return [
            'join_date' => $joinDate,
            'cutoff' => $cutoff,
            'bulan_kerja' => $bulanKerja,
            'teks' => $teks,
            'berhak' => $berhak,
            'is_direksi' => $isDireksi,
            'keterangan' => $keterangan,
        ];
    }

    private function isDireksi($emp): bool
    {
        return strcasecmp(trim((string) ($emp->divisi ?? '')), 'Direksi') === 0;
    }

    private function calculateBulanKerjaTahunBonus(?Carbon $joinDate, int $tahunBonus, bool $isDireksi = false): int
    {
        if ($isDireksi) {
            return 12;
        }

        if (!$joinDate) {
            return 0;
        }

        $joinYear = $joinDate->year;

        if ($joinYear < $tahunBonus) {
            return 12;
        }

        if ($joinYear === $tahunBonus) {
            return max(0, min(12, 12 - $joinDate->month + 1));
        }

        return 0;
    }

    private function calculateBonusNominal(int $bulanKerja, string $skema, float $basicSalary, float $customNominal = 0): array
    {
        if ($skema === 'custom') {
            $nominal = $customNominal;
            $multiplier = $basicSalary > 0 ? round($nominal / $basicSalary, 2) : 1.00;
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
            $multiplier = round($prorataMultiplier, 4);
            $nominal = round(($basicSalary / 12) * $bulanKerja, 0);
        }

        return [
            'multiplier' => $multiplier,
            'nominal' => $nominal,
        ];
    }

    private function determineJoinDate($emp): ?Carbon
    {
        $raw = $emp->awal_probation ?? $emp->awal_kontrak ?? $emp->awal_tetap ?? $emp->created_at;
        return $raw ? Carbon::parse($raw)->startOfDay() : null;
    }

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

    private function determineStatusKaryawan($emp, Carbon $cutoff): string
    {
        if (!empty($emp->resigned_at) && (string) $emp->resigned_at !== '1' && Carbon::parse($emp->resigned_at)->lte(now())) {
            return 'Resign';
        }

        if (!empty($emp->awal_tetap) && Carbon::parse($emp->awal_tetap)->lte(now())) {
            return 'Karyawan Tetap';
        }

        if (!empty($emp->awal_kontrak) && Carbon::parse($emp->awal_kontrak)->lte(now())) {
            return 'Karyawan Kontrak';
        }

        if (!empty($emp->awal_probation) && Carbon::parse($emp->awal_probation)->lte(now())) {
            return 'Karyawan Probation';
        }

        return 'Karyawan Tetap';
    }

    private function getBasicSalaryForEmployee($emp, int $tahun): float
    {
        $log = LogGaji::where('id_karyawan', $emp->id)
            ->where('tahun', '<=', $tahun)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->first();

        if ($log && ((float) ($log->gaji ?? 0) > 0)) {
            return (float) ($log->gaji ?? 0) + (float) ($log->tunjangan_jabatan ?? 0);
        }

        $gaji = (float) ($emp->gaji ?? 0);
        $tunjanganJabatan = (float) ($emp->tunjangan_jabatan ?? 0);

        return $gaji + $tunjanganJabatan;
    }
}