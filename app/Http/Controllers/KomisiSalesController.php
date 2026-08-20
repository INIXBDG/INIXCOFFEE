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

        return response()->json([
            'has_password' => (bool) $lock,
            'is_locked' => !$isUnlocked,
        ]);
    }

    public function setupLockPassword(Request $request)
    {
        $validated = $request->validate([
            'login_password' => 'required|string',
            'new_password'   => 'required|string|min:4|confirmed',
        ]);

        $user = auth()->user();

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

        ApprovalPendapatanLock::updateOrCreate(
            ['id' => 1],
            [
                'password'   => Hash::make($validated['new_password']),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]
        );

        session([
            'komisi_sales_unlocked' => true,
            'komisi_sales_unlocked_by' => $user->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Password berhasil dibuat.']);
    }

    public function unlock(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string',
            'type'     => 'required|in:approval,login',
        ]);

        $user = auth()->user();

        if ($validated['type'] === 'approval') {
            $lock = ApprovalPendapatanLock::first();
            if (!$lock || !Hash::check($validated['password'], $lock->password)) {
                return response()->json(['success' => false, 'message' => 'Password approval salah.'], 401);
            }
        } else {
            if (!Hash::check($validated['password'], $user->password)) {
                return response()->json(['success' => false, 'message' => 'Password login salah.'], 401);
            }
        }

        session([
            'komisi_sales_unlocked' => true,
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

        if (!$lock || !Hash::check($validated['current_password'], $lock->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        $lock->update([
            'password'   => Hash::make($validated['new_password']),
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Password berhasil diubah.']);
    }

    public function get($tahun, $quartal)
    {
        $tahun = (int) $tahun;
        $quartal = (int) $quartal;

        $query = ApprovalPendapatan::with(['dataMateri', 'dataPerusahaan', 'rkm'])
            ->where('status', 'valid')
            ->whereYear('tanggal_mulai', $tahun);

        if ($quartal >= 1 && $quartal <= 4) {
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

        // Target penjualan per tahun (tidak per-quartal), keyBy id_sales = kode_karyawan
        $targets = TargetPenjualan::where('tahun', $tahun)
            ->get()
            ->keyBy(fn ($t) => (string) $t->id_sales);

        $tabs = [];
        $data = [];

        foreach ($salesUsers as $kodeKaryawan => $user) {
            $items = $grouped->get((string) $kodeKaryawan, collect());

            $rows = $items->map(function ($r) {
                return [
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
                'pengurangan_PPH' => $rows->sum('pengurangan_pph'),
                'exam' => $rows->sum('exam'),
                'nett' => $rows->sum('nett'),
            ];

            $komisi = (int) round($totals['nett'] * 0.02);

            // Target & pencapaian
            $targetPenjualan = (float) ($targets->get((string) $kodeKaryawan)->nilai_target ?? 0);
            $pencapaian = $targetPenjualan > 0
                ? round(($totals['nett'] / $targetPenjualan) * 100, 2)
                : 0;

            $data[(string) $kodeKaryawan] = [
                'nama' => $user->nama_lengkap,
                'rows' => $rows,
                'totals' => $totals,
                'komisi' => $komisi,
                'terbilang' => $komisi > 0
                    ? ucfirst(trim($this->terbilang($komisi))) . ' rupiah'
                    : 'Nol rupiah',
                'target' => $targetPenjualan,
                'pencapaian' => $pencapaian,
            ];

            $tabs[] = [
                'id' => (string) $kodeKaryawan,
                'nama' => $user->nama_lengkap,
                'initials' => $this->initials($user->nama_lengkap),
            ];
        }

        usort($tabs, fn ($a, $b) => strcmp($a['nama'], $b['nama']));

        return response()->json([
            'tabs' => $tabs,
            'data' => $data,
            'tanggal_ttd' => 'Bandung, ' . Carbon::now()->locale('id')->translatedFormat('d F Y'),
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

    private function initials($nama)
    {
        $parts = array_values(array_filter(explode(' ', trim($nama))));
        $init = strtoupper(substr($parts[0] ?? '-', 0, 1));
        if (isset($parts[1])) {
            $init .= strtoupper(substr($parts[1], 0, 1));
        }
        return $init ?: '-';
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
}