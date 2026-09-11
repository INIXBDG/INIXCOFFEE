<?php

namespace App\Http\Controllers;

use App\Models\ApprovalPendapatan;
use App\Models\karyawan;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TargetPenjualan;
use App\Models\ApprovalPendapatanLock;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KomisiSalesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('office.komisiSales.index');
    }

    public function checkLockStatus()
    {
        $lock = ApprovalPendapatanLock::first();
        $unlockedBy = session('komisi_sales_unlocked_by');
        $currentUser = auth()->id();

        $isUnlocked = session('komisi_sales_unlocked', false)
            && $unlockedBy === $currentUser;

        $hasFeaturePassword = (bool) ($lock && $lock->password_komisi);
        $hasAccountingPassword = (bool) ($lock && $lock->password_accounting);

        return response()->json([
            'has_password'              => $hasFeaturePassword,
            'is_locked'                 => !$isUnlocked,
            'has_accounting_password'   => $hasAccountingPassword,
            'needs_accounting_setup'    => $hasFeaturePassword && !$hasAccountingPassword,
        ]);
    }

    public function setupLockPassword(Request $request)
    {
        $validated = $request->validate([
            'login_password' => 'required|string',
            'new_password'   => 'required|string|min:4|confirmed',
            'accounting_password' => 'nullable|string|min:4|confirmed',
        ]);

        $user = auth()->user();
        $lock = ApprovalPendapatanLock::first();

        if ($lock && ($lock->password_approval || $lock->password_komisi) && !$lock->password_accounting) {
            throw ValidationException::withMessages([
                'login_password' => 'Password Accounting belum diatur. Silakan setup Password Accounting terlebih dahulu.',
            ]);
        }

        if ($lock && $lock->password_accounting) {
            if (!Hash::check($validated['login_password'], $lock->password_accounting)) {
                throw ValidationException::withMessages([
                    'login_password' => 'Password Accounting tidak sesuai.',
                ]);
            }
        } else {
            if (!in_array($user->jabatan, ['Finance & Accounting'])) {
                throw ValidationException::withMessages([
                    'login_password' => 'Hanya user dengan jabatan Finance & Accounting yang dapat mengatur akses ini.',
                ]);
            }
            if (!Hash::check($validated['login_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'login_password' => 'Password login tidak sesuai.',
                ]);
            }
        }

        $data = [
            'password_komisi' => Hash::make($validated['new_password']),
            'updated_by'      => $user->id,
        ];

        if (!$lock || !$lock->password_accounting) {
            $accPass = $validated['accounting_password'] ?? $validated['login_password'];
            $data['password_accounting'] = Hash::make($accPass);
            $data['created_by'] = $user->id;
        }

        ApprovalPendapatanLock::updateOrCreate(['id' => 1], $data);

        session([
            'komisi_sales_unlocked'    => true,
            'komisi_sales_unlocked_by' => $user->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Password Komisi Sales berhasil dibuat/diubah.']);
    }

    public function unlock(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string',
            'type'     => 'required|in:approval,login',
        ]);

        $user = auth()->user();
        $lock = ApprovalPendapatanLock::first();

        if ($validated['type'] === 'approval') {
            if (!$lock || !$lock->password_komisi || !Hash::check($validated['password'], $lock->password_komisi)) {
                return response()->json(['success' => false, 'message' => 'Password approval salah.'], 401);
            }
        } else {
            if (!Hash::check($validated['password'], $user->password)) {
                return response()->json(['success' => false, 'message' => 'Password login salah.'], 401);
            }
        }

        session([
            'komisi_sales_unlocked'    => true,
            'komisi_sales_unlocked_by' => $user->id,
        ]);

        return response()->json(['success' => true]);
    }

    public function changeLockPassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:4|confirmed',
        ]);

        $lock = ApprovalPendapatanLock::first();

        if (!$lock || !$lock->password_komisi || !Hash::check($validated['current_password'], $lock->password_komisi)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password Komisi Sales saat ini tidak sesuai.',
            ]);
        }

        $lock->update([
            'password_komisi' => Hash::make($validated['new_password']),
            'updated_by'      => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Password Komisi Sales berhasil diubah.']);
    }

    public function changeAccountingPassword(Request $request)
    {
        $validated = $request->validate([
            'current_accounting_password' => 'required|string',
            'new_accounting_password'     => 'required|string|min:4|confirmed',
        ]);

        $lock = ApprovalPendapatanLock::first();

        if (!$lock || !$lock->password_accounting || !Hash::check($validated['current_accounting_password'], $lock->password_accounting)) {
            throw ValidationException::withMessages([
                'current_accounting_password' => 'Password Accounting saat ini tidak sesuai.',
            ]);
        }

        $lock->update([
            'password_accounting' => Hash::make($validated['new_accounting_password']),
            'updated_by'          => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Password Accounting berhasil diubah.']);
    }

    public function setupAccountingPassword(Request $request)
    {
        $validated = $request->validate([
            'login_password'          => 'required|string',
            'accounting_password'     => 'required|string|min:4|confirmed',
        ]);

        $user = auth()->user();
        $lock = ApprovalPendapatanLock::first();

        if ($lock && $lock->password_accounting) {
            return response()->json([
                'success' => false,
                'message' => 'Password Accounting sudah pernah diatur.',
            ], 422);
        }

        if (!in_array($user->jabatan, ['Finance & Accounting'])) {
            throw ValidationException::withMessages([
                'login_password' => 'Hanya user dengan jabatan Finance & Accounting yang dapat mengatur ini.',
            ]);
        }

        if (!Hash::check($validated['login_password'], $user->password)) {
            throw ValidationException::withMessages([
                'login_password' => 'Password login tidak sesuai.',
            ]);
        }

        if (!$lock || (!$lock->password_approval && !$lock->password_komisi)) {
            return response()->json([
                'success' => false,
                'message' => 'Password fitur belum tersedia.',
            ], 422);
        }

        $lock->update([
            'password_accounting' => Hash::make($validated['accounting_password']),
            'updated_by'          => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password Accounting berhasil disimpan. Silakan lanjutkan.',
        ]);
    }

    public function get($tahun, $quartal, $bulan = 0)
    {
        $tahun = (int) $tahun;
        $quartal = (int) $quartal;
        $bulan = (int) $bulan;

        $query = ApprovalPendapatan::with(['dataMateri', 'dataPerusahaan', 'rkm'])
            ->where('status', 'valid')
            ->whereYear('tanggal_mulai', $tahun);

        if ($bulan >= 1 && $bulan <= 12) {
            $query->whereMonth('tanggal_mulai', $bulan);
        } elseif ($quartal >= 1 && $quartal <= 4) {
            $query->whereMonth('tanggal_mulai', '>=', ($quartal * 3) - 2)
                ->whereMonth('tanggal_mulai', '<=', $quartal * 3);
        }

        $records = $query->get();

        $grouped = $records->groupBy(fn ($r) => (string) ($r->rkm?->sales_key ?? 'unknown'));
        $grouped->forget('unknown');
        $grouped->forget('');

        $salesUsers = karyawan::where('status_aktif', '1')
            ->whereIn('jabatan', ['Sales', 'SPV Sales'])
            ->get()
            ->keyBy('kode_karyawan');

        $targets = TargetPenjualan::where('tahun', $tahun)
            ->get()
            ->keyBy(fn ($t) => (string) $t->id_sales);

        $tabs = [];
        $data = [];

        foreach ($salesUsers as $kodeKaryawan => $user) {
            $items = $grouped->get((string) $kodeKaryawan, collect());

            $rows = $items->map(function ($r) {
                return [
                    'id_rkm' => $r->id_rkm,
                    'materi' => $r->dataMateri?->nama_materi ?? '-',
                    'pax' => (int) ($r->pax ?? 0),
                    'penjualan' => (float) ($r->total_penjualan_kotor ?? 0),
                    'discount' => (float) ($r->total_diskon ?? 0),
                    'pa' => (float) ($r->total_pa ?? 0),
                    'cashback' => (float) ($r->total_cashback ?? 0),
                    'uang_saku' => (float) ($r->total_uang_saku ?? 0),
                    'akomodasi' => (float) ($r->total_akomodasi ?? 0),
                    'transport' => (float) ($r->biaya_transport ?? 0),
                    'oleh_oleh' => (float) ($r->oleh_oleh ?? 0),
                    'entertainment' => (float) ($r->entertainment ?? 0),
                    'biaya_lainnya' => (float) ($r->biaya_lain_lain ?? 0),
                    'pengurangan_PPH' => (float) ($r->pengurangan_pph ?? 0),
                    'exam' => (float) ($r->exam ?? 0),
                    'nett' => (float) ($r->total_penjualan_bersih ?? 0),
                    'perusahaan' => $r->dataPerusahaan?->nama_perusahaan ?? '-',
                ];
            })->values();

            $totals = [
                'pax' => $rows->sum('pax'),
                'penjualan' => $rows->sum('penjualan'),
                'discount' => $rows->sum('discount'),
                'pa' => $rows->sum('pa'),
                'cashback' => $rows->sum('cashback'),
                'uang_saku' => $rows->sum('uang_saku'),
                'akomodasi' => $rows->sum('akomodasi'),
                'transport' => $rows->sum('transport'),
                'oleh_oleh' => $rows->sum('oleh_oleh'),
                'entertainment' => $rows->sum('entertainment'),
                'biaya_lainnya' => $rows->sum('biaya_lainnya'),
                'pengurangan_PPH' => $rows->sum('pengurangan_PPH'),
                'exam' => $rows->sum('exam'),
                'nett' => $rows->sum('nett'),
            ];

            $komisi = (int) round($totals['nett'] * 0.02);

            $targetPenjualan = (float) ($targets->get((string) $kodeKaryawan)->nilai_target ?? 0);
            
            $targetPeriode = $targetPenjualan;
            if ($bulan >= 1 && $bulan <= 12) {
                $targetPeriode = $targetPenjualan / 12;
            } elseif ($quartal >= 1 && $quartal <= 4) {
                $targetPeriode = $targetPenjualan / 4;
            }
            
            $pencapaian = $targetPeriode > 0
                ? round(($totals['nett'] / $targetPeriode) * 100, 2)
                : 0;

            $data[(string) $kodeKaryawan] = [
                'nama' => $user->nama_lengkap,
                'rows' => $rows,
                'totals' => $totals,
                'komisi' => $komisi,
                'terbilang' => $komisi > 0
                    ? ucfirst(trim($this->terbilang($komisi))) . ' rupiah'
                    : 'Nol rupiah',
                'target' => $targetPeriode,
                'pencapaian' => $pencapaian,
            ];

            $tabs[] = [
                'id' => (string) $kodeKaryawan,
                'nama' => $user->nama_lengkap,
                'initials' => $kodeKaryawan, 
            ];
        }

        usort($tabs, fn ($a, $b) => strcmp($a['nama'], $b['nama']));

        return response()->json([
            'tabs' => $tabs,
            'data' => $data,
            'tanggal_ttd' => 'Bandung, ' . Carbon::now()->locale('id')->translatedFormat('l, d F Y'),
        ]);
    }

    private function bulanRangeQuartal(int $quartal): string
    {
        $map = [
            0 => 'Januari - Desember',
            1 => 'Januari - Maret',
            2 => 'April - Juni',
            3 => 'Juli - September',
            4 => 'Oktober - Desember',
        ];

        return $map[$quartal] ?? '-';
    }

    public function exportPdf(Request $request)
    {
        $payload = $request->isJson() ? $request->all() : json_decode($request->getContent(), true);
        if (!$payload) $payload = $request->all();

        $dataFooter = karyawan::where('status_aktif', '1')
            ->whereIn('jabatan', ['SPV Sales', 'GM', 'Finance & Accounting'])
            ->get();

        $tahun = (int) ($payload['tahun'] ?? 0);
        $quartal = isset($payload['quartal']) ? (int) $payload['quartal'] : null;

        if (!$tahun || $quartal === null) {
            return response()->json([
                'message' => 'Parameter tahun/quartal tidak ditemukan. Silakan pilih filter periode terlebih dahulu.',
            ], 422);
        }

        $data = [
            'nama_sales' => $payload['nama_sales'] ?? 'Nama Sales',
            'tahun' => $tahun,
            'quartal' => $quartal,
            'bulan_range' => $this->bulanRangeQuartal($quartal),
            'tanggal_ttd' => $payload['tanggal_ttd'] ?? '',
            'rows' => isset($payload['rows']) && is_string($payload['rows'])
                ? json_decode($payload['rows'], true)
                : ($payload['rows'] ?? []),
            'totals' => isset($payload['totals']) && is_string($payload['totals'])
                ? json_decode($payload['totals'], true)
                : ($payload['totals'] ?? []),
            'komisi' => (float) ($payload['komisi'] ?? 0),
            'terbilang' => $payload['terbilang'] ?? '',
            'target' => (float) ($payload['target'] ?? 0),
            'pencapaian' => (float) ($payload['pencapaian'] ?? 0),
            'spv_sales' => $dataFooter->firstWhere('jabatan', 'SPV Sales'),
            'gm' => $dataFooter->firstWhere('jabatan', 'GM'),
            'finance' => $dataFooter->firstWhere('jabatan', 'Finance & Accounting'),
        ];

        $pdf = Pdf::loadView('office.komisiSales.export_pdf', $data);

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="Komisi_' . str_replace(' ', '_', $data['nama_sales']) . '.pdf"');
    }

    private function terbilang($angka)
    {
        $angka = (int) abs($angka);
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($angka < 12) return trim($huruf[$angka]);
        if ($angka < 20) return $this->terbilang($angka - 10) . ' belas';
        if ($angka < 100) return $this->terbilang(intdiv($angka, 10)) . ' puluh ' . $this->terbilang($angka % 10);
        if ($angka < 200) return 'seratus ' . $this->terbilang($angka - 100);
        if ($angka < 1000) return $this->terbilang(intdiv($angka, 100)) . ' ratus ' . $this->terbilang($angka % 100);
        if ($angka < 2000) return 'seribu ' . $this->terbilang($angka - 1000);
        if ($angka < 1000000) return $this->terbilang(intdiv($angka, 1000)) . ' ribu ' . $this->terbilang($angka % 1000);
        if ($angka < 1000000000) return $this->terbilang(intdiv($angka, 1000000)) . ' juta ' . $this->terbilang($angka % 1000000);
        if ($angka < 1000000000000) return $this->terbilang(intdiv($angka, 1000000000)) . ' miliar ' . $this->terbilang($angka % 1000000000);
        return $this->terbilang(intdiv($angka, 1000000000000)) . ' triliun ' . $this->terbilang($angka % 1000000000000);
    }

    public function updateInline(Request $request, $id_rkm)
    {
        $validated = $request->validate([
            'field' => 'required|string',
            'value' => 'nullable',
        ]);

        $approval = ApprovalPendapatan::where('id_rkm', $id_rkm)->first();

        if (!$approval) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        $field = $validated['field'];
        $newValue = $validated['value'];

        $fieldMap = [
            'total_penjualan_kotor'  => 'total_penjualan_kotor',
            'total_diskon'           => 'total_diskon',
            'total_pa'               => 'total_pa',
            'total_cashback'         => 'total_cashback',
            'total_uang_saku'        => 'total_uang_saku',
            'total_akomodasi'        => 'total_akomodasi',
            'biaya_transport'        => 'biaya_transport',
            'oleh_oleh'              => 'oleh_oleh',
            'entertainment'          => 'entertainment',
            'biaya_lain_lain'        => 'biaya_lain_lain',
            'pengurangan_pph'        => 'pengurangan_pph',
            'exam'                   => 'exam',
            'total_penjualan_bersih' => 'total_penjualan_bersih',
            'pax'                    => 'pax',
        ];

        $dbField = $fieldMap[$field] ?? null;

        if (!$dbField) {
            return response()->json(['success' => false, 'message' => 'Field tidak valid: ' . $field], 400);
        }

        if ($dbField === 'pax') {
            $newValue = (int) $newValue;
        } else {
            $newValue = (float) $newValue;
        }

        $oldValue = $approval->{$dbField};

        if ((string) $oldValue === (string) $newValue) {
            return response()->json(['success' => true, 'message' => 'Tidak ada perubahan.']);
        }

        $approval->forceFill([$dbField => $newValue]);

        if ($dbField !== 'total_penjualan_bersih') {
            $kotor = (float) ($dbField === 'total_penjualan_kotor' ? $newValue : ($approval->total_penjualan_kotor ?? 0));
            $deductions =
                (float) ($dbField === 'total_diskon' ? $newValue : ($approval->total_diskon ?? 0))
                + (float) ($dbField === 'total_pa' ? $newValue : ($approval->total_pa ?? 0))
                + (float) ($dbField === 'total_cashback' ? $newValue : ($approval->total_cashback ?? 0))
                + (float) ($dbField === 'total_uang_saku' ? $newValue : ($approval->total_uang_saku ?? 0))
                + (float) ($dbField === 'total_akomodasi' ? $newValue : ($approval->total_akomodasi ?? 0))
                + (float) ($dbField === 'biaya_transport' ? $newValue : ($approval->biaya_transport ?? 0))
                + (float) ($dbField === 'oleh_oleh' ? $newValue : ($approval->oleh_oleh ?? 0))
                + (float) ($dbField === 'biaya_lain_lain' ? $newValue : ($approval->biaya_lain_lain ?? 0))
                + (float) ($dbField === 'entertainment' ? $newValue : ($approval->entertainment ?? 0))
                + (float) ($dbField === 'exam' ? $newValue : ($approval->exam ?? 0))
                + (float) ($dbField === 'pengurangan_pph' ? $newValue : ($approval->pengurangan_pph ?? 0));

            if (isset($approval->diskon) || array_key_exists('diskon', $approval->getAttributes())) {
                $deductions += (float) ($approval->diskon ?? 0);
            }

            $approval->total_penjualan_bersih = max(0, $kotor - $deductions);
        }

        $approval->save();

        $approval->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui.',
            'data' => [
                'id_rkm'               => $approval->id_rkm,
                'field'                => $dbField,
                'value'                => $approval->{$dbField},
                'total_penjualan_bersih' => (float) ($approval->total_penjualan_bersih ?? 0),
            ],
        ]);
    }

    public function exportExcel(Request $request)
    {
        $rawPayload = $request->input('payload');
        if ($rawPayload) {
            $payload = json_decode($rawPayload, true);
        } else {
            $payload = $request->isJson() ? $request->all() : json_decode($request->getContent(), true);
        }

        if (!$payload) {
            return response()->json(['message' => 'Payload tidak valid.'], 400);
        }

        $tahun = (int) ($payload['tahun'] ?? now()->year);
        $quartal = (int) ($payload['quartal'] ?? 0);
        $bulan = (int) ($payload['bulan'] ?? 0);
        $mode = $payload['periode_mode'] ?? 'quartal';
        $namaSales = $payload['nama_sales'] ?? 'Nama Sales';
        $rows = $payload['rows'] ?? [];
        $totals = $payload['totals'] ?? [];
        $komisi = (float) ($payload['komisi'] ?? 0);
        $terbilang = $payload['terbilang'] ?? '-';
        $target = (float) ($payload['target'] ?? 0);
        $pencapaian = (float) ($payload['pencapaian'] ?? 0);
        $tanggal_ttd = $payload['tanggal_ttd'] ?? ('Bandung, ' . \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y'));

        $dataFooter = \App\Models\karyawan::where('status_aktif', '1')
            ->whereIn('jabatan', ['SPV Sales', 'GM', 'Finance & Accounting'])
            ->get();
        $spv_sales = $dataFooter->firstWhere('jabatan', 'SPV Sales');
        $gm = $dataFooter->firstWhere('jabatan', 'GM');
        $finance = $dataFooter->firstWhere('jabatan', 'Finance & Accounting');

        $bulan_range = '';
        if ($mode === 'bulan' && $bulan >= 1 && $bulan <= 12) {
            $namaBulanArr = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $bulan_range = $namaBulanArr[$bulan - 1];
            $periodeLabel = "Periode: {$bulan_range} {$tahun}";
        } elseif ($mode === 'quartal' && $quartal >= 1 && $quartal <= 4) {
            $bulan_range = $this->bulanRangeQuartal($quartal);
            $periodeLabel = "Periode: TR {$quartal} {$tahun} ({$bulan_range})";
        } else {
            $periodeLabel = "Periode: Tahunan {$tahun}";
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Komisi Sales');
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        $sheet->mergeCells('A1:Q1');
        $sheet->setCellValue('A1', 'PT. INIXINDO BANDUNG');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->mergeCells('A2:Q2');
        $sheet->setCellValue('A2', 'LAPORAN KOMISI SALES');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->mergeCells('A3:Q3');
        $sheet->setCellValue('A3', $periodeLabel);
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->setCellValue('A4', 'Nama Sales : ' . $namaSales);
        $sheet->getStyle('A4')->getFont()->setSize(10);

        $sheet->getRowDimension(5)->setRowHeight(10);

        $headers = ['No', 'Materi', 'Pax', 'Penjualan', 'Discount', 'PA', 'Cashback', 'Uang Saku', 'Akomodasi', 'Transport', 'Oleh-oleh', 'Entertainment', 'Biaya Lain', 'Pengurangan PPH', 'Exam', 'NETT', 'Perusahaan'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '6', $header);
            $col++;
        }

        $sheet->getStyle('A6:Q6')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8E8E8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $rowNum = 7;
        $no = 1;
        
        foreach ($rows as $row) {
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $row['materi'] ?? '-');
            $sheet->setCellValue('C' . $rowNum, $row['pax'] ?? 0);
            
            $numericFields = ['penjualan', 'discount', 'pa', 'cashback', 'uang_saku', 'akomodasi', 'transport', 'oleh_oleh', 'entertainment', 'biaya_lainnya', 'pengurangan_PPH', 'exam', 'nett'];
            $colLetter = 'D';
            foreach ($numericFields as $field) {
                $val = (float) ($row[$field] ?? 0);
                $sheet->setCellValue($colLetter . $rowNum, $val);
                $sheet->getStyle($colLetter . $rowNum)->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle($colLetter . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $colLetter++;
            }
            
            $sheet->setCellValue('Q' . $rowNum, $row['perusahaan'] ?? '-');

            $sheet->getStyle('A' . $rowNum . ':Q' . $rowNum)->applyFromArray([
                'font' => ['size' => 8],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            
            if ($rowNum % 2 == 0) {
                $sheet->getStyle('A' . $rowNum . ':Q' . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5F5F5');
            }
            $rowNum++;
        }

        $sheet->setCellValue('A' . $rowNum, 'TOTAL');
        $sheet->mergeCells('A' . $rowNum . ':B' . $rowNum);
        $sheet->getStyle('A' . $rowNum . ':B' . $rowNum)->applyFromArray(['font' => ['bold' => true, 'size' => 8], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
        
        $sheet->setCellValue('C' . $rowNum, $totals['pax'] ?? 0);
        $sheet->getStyle('C' . $rowNum)->applyFromArray(['font' => ['bold' => true, 'size' => 8], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
        
        $colLetter = 'D';
        $totalFields = ['penjualan', 'discount', 'pa', 'cashback', 'uang_saku', 'akomodasi', 'transport', 'oleh_oleh', 'entertainment', 'biaya_lainnya', 'pengurangan_PPH', 'exam', 'nett'];
        foreach ($totalFields as $field) {
            $val = (float) ($totals[$field] ?? 0);
            $sheet->setCellValue($colLetter . $rowNum, $val);
            $sheet->getStyle($colLetter . $rowNum)->applyFromArray([
                'numberFormat' => ['formatCode' => '"Rp" #,##0'],
                'font' => ['bold' => true, 'size' => 8],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                'borders' => ['top' => ['borderStyle' => Border::BORDER_THICK]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E6F2']]
            ]);
            $colLetter++;
        }
        $sheet->setCellValue('Q' . $rowNum, '');
        $sheet->getStyle('A' . $rowNum . ':Q' . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $rowNum += 2; 

        $sheet->mergeCells('A' . $rowNum . ':Q' . ($rowNum + 2));
        $sheet->getStyle('A' . $rowNum . ':Q' . ($rowNum + 2))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F8FC']]
        ]);

        $sheet->setCellValue('A' . $rowNum, 'Target Penjualan ' . $tahun . ':');
        $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(9);
        if ($target > 0) {
            $sheet->setCellValue('A' . ($rowNum + 1), 'Rp ' . number_format($target, 0, ',', '.'));
            $sheet->setCellValue('A' . ($rowNum + 2), 'Pencapaian: ' . number_format($pencapaian, 2, ',', '.') . '%');
            $sheet->getStyle('A' . ($rowNum + 2))->getFont()->setSize(8)->getColor()->setARGB('FF555555');
        } else {
            $sheet->setCellValue('A' . ($rowNum + 1), 'Belum ditentukan');
            $sheet->getStyle('A' . ($rowNum + 1))->getFont()->setSize(8)->getColor()->setARGB('FF999999');
        }

        $sheet->setCellValue('F' . $rowNum, 'Komisi Sales (2%):');
        $sheet->getStyle('F' . $rowNum)->getFont()->setBold(true)->setSize(9);
        $sheet->setCellValue('F' . ($rowNum + 1), 'Rp ' . number_format($komisi, 0, ',', '.'));
        $sheet->getStyle('F' . ($rowNum + 1))->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FF000000');

        $sheet->setCellValue('K' . $rowNum, 'Terbilang:');
        $sheet->getStyle('K' . $rowNum)->getFont()->setBold(true)->setSize(9);
        $sheet->mergeCells('K' . ($rowNum + 1) . ':Q' . ($rowNum + 2));
        $sheet->setCellValue('K' . ($rowNum + 1), '"' . ucfirst($terbilang) . '"');
        $sheet->getStyle('K' . ($rowNum + 1))->getFont()->setItalic(true)->setSize(9);

        $rowNum += 4; 

        $sheet->setCellValue('A' . $rowNum, $tanggal_ttd);
        $sheet->getStyle('A' . $rowNum)->getFont()->setSize(9);
        $rowNum += 2;

        $sigRow = $rowNum;
        $signatures = [
            ['label' => 'Dibuat oleh:', 'name' => $finance->nama_lengkap ?? '-', 'role' => 'Accounting', 'col' => 'A'],
            ['label' => 'Diketahui Oleh:', 'name' => $spv_sales->nama_lengkap ?? '-', 'role' => 'Sales Supervisor', 'col' => 'E'],
            ['label' => 'Disetujui oleh:', 'name' => $gm->nama_lengkap ?? '-', 'role' => 'General Manager', 'col' => 'I'],
            ['label' => 'Diterima Oleh:', 'name' => $namaSales, 'role' => 'Sales & Marketing', 'col' => 'M'],
        ];

        foreach ($signatures as $sig) {
            $colStart = $sig['col'];
            $colEnd = chr(ord($colStart) + 3);
            
            $sheet->mergeCells($colStart . $sigRow . ':' . $colEnd . $sigRow);
            $sheet->setCellValue($colStart . $sigRow, $sig['label']);
            $sheet->getStyle($colStart . $sigRow)->getFont()->setSize(8)->getColor()->setARGB('FF666666');
            
            $sheet->getRowDimension($sigRow + 1)->setRowHeight(40);
            
            $sheet->mergeCells($colStart . ($sigRow + 2) . ':' . $colEnd . ($sigRow + 2));
            $sheet->setCellValue($colStart . ($sigRow + 2), $sig['name']);
            $sheet->getStyle($colStart . ($sigRow + 2))->getFont()->setBold(true)->setSize(9);
            
            $sheet->mergeCells($colStart . ($sigRow + 3) . ':' . $colEnd . ($sigRow + 3));
            $sheet->setCellValue($colStart . ($sigRow + 3), $sig['role']);
            $sheet->getStyle($colStart . ($sigRow + 3))->getFont()->setSize(8)->getColor()->setARGB('FF555555');
            
            $sheet->getStyle($colStart . $sigRow . ':' . $colEnd . ($sigRow + 3))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getColumnDimension('A')->setWidth(5);   // No
        $sheet->getColumnDimension('B')->setWidth(35);  // Materi
        $sheet->getColumnDimension('C')->setWidth(8);   // Pax
        $sheet->getColumnDimension('D')->setWidth(15);  // Penjualan
        $sheet->getColumnDimension('E')->setWidth(12);  // Discount
        $sheet->getColumnDimension('F')->setWidth(12);  // PA
        $sheet->getColumnDimension('G')->setWidth(12);  // Cashback
        $sheet->getColumnDimension('H')->setWidth(12);  // Uang Saku
        $sheet->getColumnDimension('I')->setWidth(15);  // Akomodasi
        $sheet->getColumnDimension('J')->setWidth(15);  // Transport
        $sheet->getColumnDimension('K')->setWidth(12);  // Oleh-oleh
        $sheet->getColumnDimension('L')->setWidth(12);  // Entertainment
        $sheet->getColumnDimension('M')->setWidth(12);  // Biaya Lain
        $sheet->getColumnDimension('N')->setWidth(15);  // Pengurangan PPH
        $sheet->getColumnDimension('O')->setWidth(12);  // Exam
        $sheet->getColumnDimension('P')->setWidth(15);  // NETT
        $sheet->getColumnDimension('Q')->setWidth(35);  // Perusahaan

        $fileName = 'Komisi_Sales_' . str_replace(' ', '_', $namaSales);
        if ($mode === 'bulan') {
            $fileName .= '_Bulan' . $bulan . '_' . $tahun . '.xlsx';
        } elseif ($mode === 'quartal') {
            $fileName .= '_TR' . $quartal . '_' . $tahun . '.xlsx';
        } else {
            $fileName .= '_Tahunan_' . $tahun . '.xlsx';
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}