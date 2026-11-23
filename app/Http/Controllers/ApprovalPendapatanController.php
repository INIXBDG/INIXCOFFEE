<?php

namespace App\Http\Controllers;

use App\Models\ApprovalPendapatan;
use App\Models\Invoice;
use App\Models\Materi;
use App\Models\outstanding;
use App\Models\Perusahaan;
use App\Models\PicPenagihanInvoice;
use App\Models\RKM;
use App\Models\trackingOutstanding;
use Carbon\CarbonImmutable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ApprovalPendapatanLock;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class ApprovalPendapatanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View ApprovalPendapatan', ['only' => ['index', 'get']]);
        $this->middleware('permission:Update ApprovalPendapatan', ['only' => ['update']]);
    }

    public function index()
    {
        $dataMateri = Materi::get();
        $dataPerusahaan = Perusahaan::get();

        return view('office.approvalPendapatan.index', compact('dataMateri', 'dataPerusahaan'));
    }

    public function checkLockStatus()
    {
        $lock = ApprovalPendapatanLock::first();
        $unlockedBy = session('approval_pendapatan_unlocked_by');
        $currentUser = auth()->id();

        $isUnlocked = session('approval_pendapatan_unlocked', false)
            && $unlockedBy === $currentUser;

        $hasFeaturePassword = (bool) ($lock && $lock->password_approval);
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
            'password_approval' => Hash::make($validated['new_password']),
            'updated_by'        => $user->id,
        ];

        if (!$lock || !$lock->password_accounting) {
            $accPass = $validated['accounting_password'] ?? $validated['login_password'];
            $data['password_accounting'] = Hash::make($accPass);
            $data['created_by'] = $user->id;
        }

        ApprovalPendapatanLock::updateOrCreate(['id' => 1], $data);

        session([
            'approval_pendapatan_unlocked'    => true,
            'approval_pendapatan_unlocked_by' => $user->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Password Approval berhasil dibuat/diubah.']);
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
            if (!$lock || !$lock->password_approval || !Hash::check($validated['password'], $lock->password_approval)) {
                return response()->json(['success' => false, 'message' => 'Password approval salah.'], 401);
            }
        } else {
            if (!Hash::check($validated['password'], $user->password)) {
                return response()->json(['success' => false, 'message' => 'Password login salah.'], 401);
            }
        }

        session([
            'approval_pendapatan_unlocked'    => true,
            'approval_pendapatan_unlocked_by' => $user->id,
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

        if (!$lock || !$lock->password_approval || !Hash::check($validated['current_password'], $lock->password_approval)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password Approval saat ini tidak sesuai.',
            ]);
        }

        $lock->update([
            'password_approval' => Hash::make($validated['new_password']),
            'updated_by'        => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Password Approval berhasil diubah.']);
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

    public function get($tahun, $bulan)
    {
        $validate = Validator::make(
            [
                'tahun' => $tahun,
                'bulan' => $bulan,
            ],
            [
                'tahun' => 'required|integer|digits:4',
                'bulan' => 'required|integer|between:1,12',
            ],
        );

        if ($validate->fails()) {
            return response()->json(['error' => 'Parameter tahun dan bulan tidak valid'], 400);
        }

        $startDate = CarbonImmutable::create($tahun, $bulan, 1);
        $endDate = CarbonImmutable::create($tahun, $bulan, 1)->endOfMonth();
        $hariIni = now();

        $startOfWeek = $startDate->copy()->startOfWeek();
        $endOfMonth = $endDate->copy();

        $weekNumber = 1;
        $weekRanges = [];

        while ($startOfWeek->lte($endOfMonth)) {
            $endOfWeek = $startOfWeek->copy()->endOfWeek();
            $start = $startOfWeek->format('Y-m-d');
            $end = $endOfWeek->format('Y-m-d');

            $rows = Invoice::with(['rkm', 'rkm.materi', 'rkm.perusahaan', 'rkm.sales', 'rkm.instruktur', 'rkm.outstanding', 'rkm.eksam', 'rkm.outstanding.tracking_outstanding'])
                ->whereHas('rkm', function ($query) use ($start, $end, $hariIni) {
                    $query->whereBetween('tanggal_awal', [$start, $end])
                        ->whereDate('tanggal_awal', '<=', $hariIni);
                })
                ->get();

            $dataValid = ApprovalPendapatan::with(['dataMateri', 'dataPerusahaan'])
                ->whereIn('id_rkm', $rows->pluck('id_rkm'))
                ->get()
                ->keyBy('id_rkm');

            $weekData = [];
            foreach ($rows as $item) {
                $rkm = $item->rkm;
                if (!$rkm) continue;

                $valid = $dataValid->get($item->id_rkm);
                $hargaNet = (float) ($valid?->harga_net ?? ($item->harga_jual ?? 0));
                $pax = (int) ($valid?->pax ?? ($item->pax ?? 0));
                $total = (float) ($valid?->total_penjualan_kotor ?? $hargaNet * $pax);

                $weekData[] = [
                    'id_rkm' => $rkm->id,
                    'no_faktur' => $valid?->no_faktur ?? 'belum ada',
                    'no_invoice' => $valid?->no_invoice ?? 'belum ada',
                    'materi' => $valid?->dataMateri?->nama_materi ?? ($rkm->materi?->nama_materi ?? 'materi kosong'),
                    'tanggal_training' => Carbon::parse($rkm->tanggal_awal)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d F Y'),
                    'perusahaan' => $valid?->dataPerusahaan?->nama_perusahaan ?? ($rkm->perusahaan?->nama_perusahaan ?? 'perusahaan kosong'),
                    'nama_sales' => $rkm->sales?->nama_lengkap ?? 'sales kosong',
                    'instruktur' => $rkm->instruktur?->nama_lengkap ?? 'instruktur kosong',
                    'harga' => $hargaNet,
                    'pax' => $pax,
                    'total' => $total,
                    'total_penjualan_kotor' => $total,
                    'diskon' => (float) ($valid?->diskon ?? 0),
                    'total_diskon' => (float) ($valid?->total_diskon ?? 0),
                    'total_pa' => (float) ($valid?->total_pa ?? 0),
                    'total_cashback' => (float) ($valid?->total_cashback ?? 0),
                    'total_uang_saku' => (float) ($valid?->total_uang_saku ?? 0),
                    'total_akomodasi' => (float) ($valid?->total_akomodasi ?? 0),
                    'oleh_oleh' => (float) ($valid?->oleh_oleh ?? 0),
                    'biaya_lain_lain' => (float) ($valid?->biaya_lain_lain ?? 0),
                    'entertainment' => (float) ($valid?->entertainment ?? 0),
                    'total_penjualan_sales' => (float) ($valid?->total_penjualan_bersih ?? 0),
                    'PPN' => (float) ($valid?->PPN ?? 0),
                    'PPH' => (float) ($valid?->PPH ?? 0),
                    'pengurangan_pph' => (float) ($valid?->pengurangan_pph ?? 0),
                    'jumlah_pembayaran' => (float) ($valid?->jumlah_pembayaran ?? 0),
                    'tanggal_pembayaran' => $valid?->tanggal_pembayaran ?? null,
                    'biaya_admin' => (float) ($valid?->biaya_admin ?? 0),
                    'total_piutang' => (float) ($rkm->outstanding?->net_sales ?? 0),
                    'jenis_transport' => $valid?->jenis_transport ?? '-',
                    'biaya_transport' => (float) ($valid?->biaya_transport ?? 0),
                    'valid' => $valid?->status ?? 'belum tervalidasi',
                    'materi_id' => $valid?->materi ?? $rkm->materi_key,
                    'tanggal_mulai' => $valid?->tanggal_mulai ? Carbon::parse($valid->tanggal_mulai)->format('Y-m-d') : Carbon::parse($rkm->tanggal_awal)->format('Y-m-d'),
                    'tanggal_selesai' => $valid?->tanggal_selesai ? Carbon::parse($valid->tanggal_selesai)->format('Y-m-d') : Carbon::parse($rkm->tanggal_akhir)->format('Y-m-d'),
                    'perusahaan_id' => $valid?->perusahaan ?? $rkm->perusahaan_key,
                    'exam' => ($valid?->exam ?? $rkm->eksam?->total) ? 'Rp ' . number_format((float) ($valid?->exam ?? $rkm->eksam?->total), 0, ',', '.') : '-',
                    'exam_value' => (float) ($valid?->exam ?? $rkm->eksam?->total ?? 0),
                    'pic' => $rkm->outstanding?->pic ?? null,
                    'regist' => $rkm->outstanding?->no_regist ?? null,
                    'tracking' => $rkm->outstanding?->tracking_outstanding ? [
                        'invoice' => (bool)$rkm->outstanding->tracking_outstanding->invoice,
                        'faktur_pajak' => (bool)$rkm->outstanding->tracking_outstanding->faktur_pajak,
                        'dokumen_tambahan' => (bool)$rkm->outstanding->tracking_outstanding->dokumen_tambahan,
                        'konfir_cs' => (bool)$rkm->outstanding->tracking_outstanding->konfir_cs,
                        'tracking_dokumen' => (bool)$rkm->outstanding->tracking_outstanding->tracking_dokumen,
                        'no_resi' => (bool)$rkm->outstanding->tracking_outstanding->no_resi,
                        'konfir_pic' => (bool)$rkm->outstanding->tracking_outstanding->konfir_pic,
                        'pembayaran' => (bool)$rkm->outstanding->tracking_outstanding->pembayaran,
                        'status_resi' => $rkm->outstanding->tracking_outstanding->status_resi,
                        'status_pic' => $rkm->outstanding->tracking_outstanding->status_pic ?? '-',
                    ] : null,
                ];
            }

            $weekRanges[] = [
                'start' => $start,
                'end' => $end,
                'data' => $weekData,
                'week_number' => $weekNumber,
            ];

            $startOfWeek = $startOfWeek->addWeek();
            $weekNumber++;
        }

        $monthRanges = [
            'month' => $startDate->translatedFormat('F-Y'),
            'weeksData' => $weekRanges,
        ];

        $footerBulanan = ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)
            ->whereMonth('tanggal_mulai', $bulan)
            ->where('status', 'valid')
            ->selectRaw("
                SUM(CAST(total_penjualan_kotor AS UNSIGNED)) as total_penjualan,
                SUM(CAST(total_diskon AS UNSIGNED)) as total_diskon,
                SUM(CAST(total_pa AS UNSIGNED)) as total_pa,
                SUM(CAST(total_cashback AS UNSIGNED)) as total_cashback,
                SUM(CAST(total_uang_saku AS UNSIGNED)) as total_uang_saku,
                SUM(CAST(total_akomodasi AS UNSIGNED)) as total_akomodasi,
                SUM(CAST(oleh_oleh AS UNSIGNED)) as oleh_oleh,
                SUM(CAST(biaya_lain_lain AS UNSIGNED)) as biaya_lain_lain,
                SUM(CAST(entertainment AS UNSIGNED)) as entertainment,
                SUM(CAST(total_penjualan_bersih AS UNSIGNED)) as total_penjualan_sales,
                SUM(CAST(PPN AS UNSIGNED)) as total_ppn,
                SUM(CAST(PPH AS UNSIGNED)) as total_pph,
                SUM(CAST(pengurangan_pph AS UNSIGNED)) as pengurangan_pph,
                SUM(CAST(jumlah_pembayaran AS UNSIGNED)) as jumlah_pembayaran,
                SUM(CAST(biaya_admin AS UNSIGNED)) as biaya_admin,
                SUM(CAST(biaya_transport AS UNSIGNED)) as biaya_transport,
                SUM(CAST(harga_net AS UNSIGNED)) as total_harga,
                SUM(CAST(pax AS UNSIGNED)) as total_pax,
                SUM(CAST(diskon AS UNSIGNED)) as total_diskon_pa
            ")->first();

        $examBulanan = ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)
            ->whereMonth('tanggal_mulai', $bulan)
            ->where('status', 'valid')
            ->sum('exam');

        $piutangBulanan = (float) outstanding::whereIn(
            'id_rkm',
            ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)
                ->whereMonth('tanggal_mulai', $bulan)
                ->where('status', 'valid')
                ->pluck('id_rkm')
        )->sum('net_sales');

        if ($footerBulanan) {
            $footerBulanan->total_exam = $examBulanan;
        } else {
            $footerBulanan = (object) [
                'total_penjualan' => 0, 'total_diskon' => 0, 'total_pa' => 0, 'total_cashback' => 0,
                'total_uang_saku' => 0, 'total_akomodasi' => 0, 'oleh_oleh' => 0, 'biaya_lain_lain' => 0,
                'entertainment' => 0, 'total_penjualan_sales' => 0, 'total_ppn' => 0, 'total_pph' => 0,
                'pengurangan_pph' => 0, 'jumlah_pembayaran' => 0, 'biaya_admin' => 0, 'biaya_transport' => 0,
                'total_exam' => $examBulanan,'total_piutang' => 0,
            ];
        }

        $footerBulanan->total_piutang = $piutangBulanan;

        $footerTahunan = ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)
            ->where('status', 'valid')
            ->selectRaw("
                SUM(CAST(total_penjualan_kotor AS UNSIGNED)) as total_penjualan,
                SUM(CAST(total_diskon AS UNSIGNED)) as total_diskon,
                SUM(CAST(total_pa AS UNSIGNED)) as total_pa,
                SUM(CAST(total_cashback AS UNSIGNED)) as total_cashback,
                SUM(CAST(total_uang_saku AS UNSIGNED)) as total_uang_saku,
                SUM(CAST(total_akomodasi AS UNSIGNED)) as total_akomodasi,
                SUM(CAST(oleh_oleh AS UNSIGNED)) as oleh_oleh,
                SUM(CAST(biaya_lain_lain AS UNSIGNED)) as biaya_lain_lain,
                SUM(CAST(entertainment AS UNSIGNED)) as entertainment,
                SUM(CAST(total_penjualan_bersih AS UNSIGNED)) as total_penjualan_sales,
                SUM(CAST(PPN AS UNSIGNED)) as total_ppn,
                SUM(CAST(PPH AS UNSIGNED)) as total_pph,
                SUM(CAST(pengurangan_pph AS UNSIGNED)) as pengurangan_pph,
                SUM(CAST(jumlah_pembayaran AS UNSIGNED)) as jumlah_pembayaran,
                SUM(CAST(biaya_admin AS UNSIGNED)) as biaya_admin,
                SUM(CAST(biaya_transport AS UNSIGNED)) as biaya_transport,
                SUM(CAST(harga_net AS UNSIGNED)) as total_harga,
                SUM(CAST(pax AS UNSIGNED)) as total_pax,
                SUM(CAST(diskon AS UNSIGNED)) as total_diskon_pa
            ")->first();

        $examTahunan = ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)
            ->where('status', 'valid')
            ->sum('exam');

        $piutangTahunan = (float) outstanding::whereIn(
            'id_rkm',
            ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)
                ->where('status', 'valid')
                ->pluck('id_rkm')
        )->sum('net_sales');

        if ($footerTahunan) {
            $footerTahunan->total_exam = $examTahunan;
        } else {
            $footerTahunan = (object) [
                'total_penjualan' => 0, 'total_diskon' => 0, 'total_pa' => 0, 'total_cashback' => 0,
                'total_uang_saku' => 0, 'total_akomodasi' => 0, 'oleh_oleh' => 0, 'biaya_lain_lain' => 0,
                'entertainment' => 0, 'total_penjualan_sales' => 0, 'total_ppn' => 0, 'total_pph' => 0,
                'pengurangan_pph' => 0, 'jumlah_pembayaran' => 0, 'biaya_admin' => 0, 'biaya_transport' => 0,
                'total_exam' => $examTahunan,'total_piutang' => 0,
            ];
        }

        $footerTahunan->total_piutang = $piutangTahunan;

        return response()->json([
            'data' => [$monthRanges],
            'footer_bulanan' => $footerBulanan,
            'footer_tahunan' => $footerTahunan,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'no_faktur' => 'nullable|string|max:255',
            'no_invoice' => 'nullable|string|max:255',
            'harga' => 'nullable|numeric|min:0',
            'pax' => 'nullable|integer|min:0',
            'diskon' => 'nullable|numeric',
            'total_diskon' => 'nullable|numeric',
            'total_pa' => 'nullable|numeric',
            'total_cashback' => 'nullable|numeric',
            'total_uang_saku' => 'nullable|numeric',
            'total_akomodasi' => 'nullable|numeric',
            'jenis_transport' => 'nullable|string|max:255',
            'biaya_transport' => 'nullable|numeric',
            'oleh_oleh' => 'nullable|numeric',
            'biaya_lain_lain' => 'nullable|numeric|min:0',
            'entertainment' => 'nullable|numeric',
            'exam' => 'nullable|numeric|min:0',
            'total_penjualan_sales' => 'nullable|numeric|min:0',
            'PPN' => 'nullable|numeric',
            'PPH' => 'nullable|numeric',
            'pengurangan_pph' => 'nullable|numeric|min:0',
            'materi' => 'nullable',
            'perusahaan' => 'nullable',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jumlah_pembayaran' => 'nullable|numeric',
            'tanggal_pembayaran' => 'nullable',
            'biaya_admin' => 'nullable|numeric',
            'pic' => 'nullable',
            'regist' => 'nullable',
            'faktur_pajak' => 'nullable|boolean',
            'dokumen_tambahan' => 'nullable|boolean',
            'konfir_cs' => 'nullable|boolean',
            'tracking_dokumen' => 'nullable|boolean',
            'no_resi' => 'nullable|boolean',
            'konfir_pic' => 'nullable|boolean',
            'pembayaran' => 'nullable|boolean',
            'status_pic' => 'nullable',
            'total_penjualan_kotor' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $approval = ApprovalPendapatan::firstOrNew(['id_rkm' => $id]);
            $approval->fill([
                'no_faktur' => $validated['no_faktur'] ?? null,
                'no_invoice' => $validated['no_invoice'] ?? null,
                'harga_net' => (float) ($validated['harga'] ?? 0),
                'pax' => (int) ($validated['pax'] ?? 0),
                'diskon' => (float) ($validated['diskon'] ?? 0),
                'total_diskon' => (float) ($validated['total_diskon'] ?? 0),
                'total_pa' => (float) ($validated['total_pa'] ?? 0),
                'total_cashback' => (float) ($validated['total_cashback'] ?? 0),
                'total_uang_saku' => (float) ($validated['total_uang_saku'] ?? 0),
                'total_akomodasi' => (float) ($validated['total_akomodasi'] ?? 0),
                'jenis_transport' => $validated['jenis_transport'] ?? null,
                'biaya_transport' => (float) ($validated['biaya_transport'] ?? 0),
                'oleh_oleh' => (float) ($validated['oleh_oleh'] ?? 0),
                'biaya_lain_lain' => (float) ($validated['biaya_lain_lain'] ?? 0),
                'entertainment' => (float) ($validated['entertainment'] ?? 0),
                'exam' => (float) ($validated['exam'] ?? 0),
                'total_penjualan_bersih' => (float) ($validated['total_penjualan_sales'] ?? 0),
                'PPN' => (float) ($validated['PPN'] ?? 0),
                'PPH' => (float) ($validated['PPH'] ?? 0),
                'pengurangan_pph' => (float) ($validated['pengurangan_pph'] ?? 0),
                'status' => 'valid',
                'materi' => $validated['materi'] ?? null,
                'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
                'perusahaan' => $validated['perusahaan'] ?? null,
                'jumlah_pembayaran' => (float) ($validated['jumlah_pembayaran'] ?? 0),
                'tanggal_pembayaran' => $validated['tanggal_pembayaran'] ?? null,
                'biaya_admin' => (float) ($validated['biaya_admin'] ?? 0),
                'total_penjualan_kotor' => (float) ($validated['total_penjualan_kotor'] ?? 0),
            ]);
            $approval->save();

            $statusPembayaran = ($approval->jumlah_pembayaran > 0) ? '1' : '0';
            $picPenagihan = PicPenagihanInvoice::where('id_rkm', $id)->first();
            $picValue = $picPenagihan?->pic ?? null;

            $potonganData = [];
            if ($approval->PPN) $potonganData[] = ['jenis' => 'PPN', 'jumlah' => $approval->PPN];
            if ($approval->PPH) $potonganData[] = ['jenis' => 'PPH', 'jumlah' => $approval->PPH];
            if ($approval->biaya_admin) $potonganData[] = ['jenis' => 'biaya_admin', 'jumlah' => $approval->biaya_admin];

            outstanding::updateOrCreate(
                ['id_rkm' => $approval->id_rkm],
                [
                    'net_sales' => $approval->harga_net,
                    'jumlah_pembayaran' => $approval->jumlah_pembayaran,
                    'due_date' => $approval->invoice?->due_date ?? null,
                    'pic' => $validated['pic'] ?? $picValue,
                    'tanggal_bayar' => $approval->tanggal_pembayaran,
                    'status_pembayaran' => $statusPembayaran,
                    'ppn' => $approval->PPN,
                    'sales_key' => $approval->rkm?->sales_key ?? null,
                    'no_invoice' => $approval->no_invoice ?? null,
                    'no_regist' => $validated['regist'] ?? null,
                    'pph' => $approval->PPH,
                    'biaya_admin' => $approval->biaya_admin,
                    'jumlah_potongan' => !empty($potonganData) ? $potonganData : null,
                    'jenis_potongan' => !empty($potonganData) ? array_column($potonganData, 'jenis') : null,
                ]
            );

            $outstanding = outstanding::where('id_rkm', $approval->id_rkm)->first();
            if ($outstanding) {
                trackingOutstanding::updateOrCreate(
                    ['id_outstanding' => $outstanding->id],
                    [
                        'invoice' => '1',
                        'faktur_pajak' => $request->boolean('faktur_pajak') ? '1' : '0',
                        'dokumen_tambahan' => $request->boolean('dokumen_tambahan') ? '1' : '0',
                        'konfir_cs' => $request->boolean('konfir_cs') ? '1' : '0',
                        'tracking_dokumen' => $request->boolean('tracking_dokumen') ? '1' : '0',
                        'no_resi' => $request->boolean('no_resi') ? '1' : '0',
                        'konfir_pic' => $request->boolean('konfir_pic') ? '1' : '0',
                        'pembayaran' => $request->boolean('pembayaran') ? '1' : '0',
                        'status_resi' => $validated['status_resi'] ?? '-',
                        'status_pic' => $validated['status_pic'] ?? '-',
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil diupdate']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $rawPayload = $request->input('payload');
        $payload = $rawPayload ? json_decode($rawPayload, true) : $request->all();
        if (!is_array($payload)) {
            $payload = [];
        }

        $tahun = (int) now()->year;
        $mode = $payload['mode'] ?? 'bulan';
        if (!in_array($mode, ['tahunan', 'kuartal', 'bulan', 'minggu'], true)) {
            abort(422, 'Mode periode tidak valid');
        }

        $kuartal = (int) ($payload['kuartal'] ?? 1);
        $bulan = (int) ($payload['bulan'] ?? now()->month);
        $minggu = (int) ($payload['minggu'] ?? 1);
        $useFilter = filter_var($payload['use_filter'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $filter = $useFilter && is_array($payload['filter'] ?? null) ? $payload['filter'] : [];

        if ($mode === 'kuartal' && ($kuartal < 1 || $kuartal > 4)) {
            abort(422, 'Kuartal tidak valid');
        }
        if (in_array($mode, ['bulan', 'minggu'], true) && ($bulan < 1 || $bulan > 12)) {
            abort(422, 'Bulan tidak valid');
        }

        $bulanNama = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $yearStart = CarbonImmutable::create($tahun, 1, 1);
        $yearEnd = CarbonImmutable::create($tahun, 12, 31);
        $onlyWeek = null;

        if ($mode === 'tahunan') {
            $months = range(1, 12);
            $periodStart = $yearStart;
            $periodEnd = $yearEnd;
            $periodLabel = 'Tahun ' . $tahun . ' (Januari – Desember)';
            $fileSuffix = 'Tahun_' . $tahun;
        } elseif ($mode === 'kuartal') {
            $startMonth = ($kuartal - 1) * 3 + 1;
            $months = range($startMonth, $startMonth + 2);
            $periodStart = CarbonImmutable::create($tahun, $startMonth, 1);
            $periodEnd = CarbonImmutable::create($tahun, $startMonth + 2, 1)->endOfMonth();
            $periodLabel = 'Q' . $kuartal . ' ' . $tahun . ' (' . $bulanNama[$startMonth - 1] . ' – ' . $bulanNama[$startMonth + 1] . ')';
            $fileSuffix = 'Q' . $kuartal . '_' . $tahun;
        } elseif ($mode === 'bulan') {
            $months = [$bulan];
            $periodStart = CarbonImmutable::create($tahun, $bulan, 1);
            $periodEnd = $periodStart->endOfMonth();
            $periodLabel = $bulanNama[$bulan - 1] . ' ' . $tahun;
            $fileSuffix = $bulanNama[$bulan - 1] . '_' . $tahun;
        } else {
            $months = [$bulan];
            $periodStart = CarbonImmutable::create($tahun, $bulan, 1);
            $periodEnd = $periodStart->endOfMonth();
            $weekStart = $periodStart->startOfWeek()->addWeeks($minggu - 1);
            if ($minggu < 1 || $weekStart->gt($periodEnd)) {
                abort(422, 'Week tidak valid');
            }
            $weekEnd = $weekStart->addDays(4);
            $onlyWeek = $minggu;
            $periodLabel = 'Week ' . $minggu . ' ' . $bulanNama[$bulan - 1] . ' ' . $tahun
                . ' (' . $weekStart->translatedFormat('d F Y') . ' – ' . $weekEnd->translatedFormat('d F Y') . ')';
            $fileSuffix = 'Week' . $minggu . '_' . $bulanNama[$bulan - 1] . '_' . $tahun;
        }

        $periodFrom = $periodStart->format('Y-m-d');
        $periodTo = $periodEnd->format('Y-m-d');

        $records = $this->collectRows($tahun, $months, $filter, $onlyWeek);

        $footers = [];
        if ($mode === 'tahunan') {
            $footers[] = [
                'label' => 'TOTAL TAHUNAN',
                'style' => 'dark',
                'data' => $this->footerTotals($yearStart->format('Y-m-d'), $yearEnd->format('Y-m-d')),
            ];
        } else {
            $footers[] = [
                'label' => $mode === 'kuartal' ? 'TOTAL KUARTAL Q' . $kuartal : 'TOTAL BULANAN',
                'style' => 'info',
                'data' => $this->footerTotals($periodFrom, $periodTo),
            ];
            $footers[] = [
                'label' => 'TOTAL TAHUNAN',
                'style' => 'dark',
                'data' => $this->footerTotals($yearStart->format('Y-m-d'), $yearEnd->format('Y-m-d')),
            ];
        }

        $columns = [
            ['label' => 'No Faktur', 'key' => 'no_faktur', 'type' => 'text', 'footer' => null],
            ['label' => 'No Invoice', 'key' => 'no_invoice', 'type' => 'text', 'footer' => null],
            ['label' => 'Materi', 'key' => 'materi', 'type' => 'text', 'footer' => null],
            ['label' => 'Tanggal Training', 'key' => 'tanggal_training', 'type' => 'text', 'footer' => null],
            ['label' => 'Perusahaan', 'key' => 'perusahaan', 'type' => 'text', 'footer' => null],
            ['label' => 'Sales', 'key' => 'nama_sales', 'type' => 'text', 'footer' => null],
            ['label' => 'Instruktur', 'key' => 'instruktur', 'type' => 'text', 'footer' => null],
            ['label' => 'Harga', 'key' => 'harga', 'type' => 'currency', 'footer' => 'total_harga'],
            ['label' => 'Pax', 'key' => 'pax', 'type' => 'number', 'footer' => 'total_pax'],
            ['label' => 'Total Penjualan Kotor', 'key' => 'total_penjualan_kotor', 'type' => 'currency', 'footer' => 'total_penjualan'],
            ['label' => 'Diskon/PA', 'key' => 'diskon', 'type' => 'currency', 'footer' => 'total_diskon_pa'],
            ['label' => 'Total Diskon', 'key' => 'total_diskon', 'type' => 'currency', 'footer' => 'total_diskon'],
            ['label' => 'Total PA', 'key' => 'total_pa', 'type' => 'currency', 'footer' => 'total_pa'],
            ['label' => 'Cashback', 'key' => 'total_cashback', 'type' => 'currency', 'footer' => 'total_cashback'],
            ['label' => 'Uang Saku', 'key' => 'total_uang_saku', 'type' => 'currency', 'footer' => 'total_uang_saku'],
            ['label' => 'Akomodasi', 'key' => 'total_akomodasi', 'type' => 'currency', 'footer' => 'total_akomodasi'],
            ['label' => 'Oleh-Oleh Peserta', 'key' => 'oleh_oleh', 'type' => 'currency', 'footer' => 'oleh_oleh'],
            ['label' => 'Biaya Lain-Lain', 'key' => 'biaya_lain_lain', 'type' => 'currency', 'footer' => 'biaya_lain_lain'],
            ['label' => 'Entertainment', 'key' => 'entertainment', 'type' => 'currency', 'footer' => 'entertainment'],
            ['label' => 'Jenis Transport', 'key' => 'jenis_transport', 'type' => 'text', 'footer' => null],
            ['label' => 'Biaya Transport', 'key' => 'biaya_transport', 'type' => 'currency', 'footer' => 'biaya_transport'],
            ['label' => 'Pengurangan PPH', 'key' => 'pengurangan_pph', 'type' => 'currency', 'footer' => 'pengurangan_pph'],
            ['label' => 'Exam', 'key' => 'exam_value', 'type' => 'currency', 'footer' => 'total_exam'],
            ['label' => 'Total Penjualan Sales (Bersih)', 'key' => 'total_penjualan_sales', 'type' => 'currency', 'footer' => 'total_penjualan_sales'],
            ['label' => 'PPN', 'key' => 'PPN', 'type' => 'currency', 'footer' => 'total_ppn'],
            ['label' => 'PPH', 'key' => 'PPH', 'type' => 'currency', 'footer' => 'total_pph'],
            ['label' => 'Jumlah Pembayaran', 'key' => 'jumlah_pembayaran', 'type' => 'currency', 'footer' => 'jumlah_pembayaran'],
            ['label' => 'Tanggal Pembayaran', 'key' => 'tanggal_pembayaran', 'type' => 'text', 'footer' => null],
            ['label' => 'Biaya Admin', 'key' => 'biaya_admin', 'type' => 'currency', 'footer' => 'biaya_admin'],
            ['label' => 'Total Piutang', 'key' => 'total_piutang', 'type' => 'currency', 'footer' => 'total_piutang'],
            ['label' => 'Tanggal Mulai', 'key' => 'tanggal_mulai', 'type' => 'text', 'footer' => null],
            ['label' => 'Tanggal Selesai', 'key' => 'tanggal_selesai', 'type' => 'text', 'footer' => null],
            ['label' => 'Status', 'key' => 'valid', 'type' => 'text', 'footer' => null],
        ];

        $totalCols = count($columns) + 1;
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);
        $labelEndCol = Coordinate::stringFromColumnIndex(8);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Approval Penjualan');

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'PT. INIXINDO AMIETE MANDIRI BANDUNG');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('2A3A4D');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'LAPORAN APPROVAL PENJUALAN');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('5E8BC2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'Periode: ' . $periodLabel);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('6B7C93');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headerRow = 5;
        $sheet->setCellValue('A' . $headerRow, 'No');
        foreach ($columns as $i => $col) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 2) . $headerRow, $col['label']);
        }
        $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('7AA4D4');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('5E8BC2');
        $sheet->getRowDimension($headerRow)->setRowHeight(30);

        $rowNum = $headerRow + 1;
        $dataStart = $rowNum;
        $totalRows = count($records);
        $validCount = 0;

        if ($totalRows === 0) {
            $sheet->mergeCells("A{$rowNum}:{$lastCol}{$rowNum}");
            $sheet->setCellValue("A{$rowNum}", 'Tidak ada data pada periode ini');
            $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('6B7C93');
            $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowNum++;
        } else {
            foreach ($records as $index => $record) {
                $sheet->setCellValue('A' . $rowNum, $index + 1);

                foreach ($columns as $i => $col) {
                    $cell = Coordinate::stringFromColumnIndex($i + 2) . $rowNum;
                    $value = $record[$col['key']] ?? null;

                    if ($col['type'] === 'currency') {
                        $sheet->setCellValue($cell, (float) $value);
                        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
                    } elseif ($col['type'] === 'number') {
                        $sheet->setCellValue($cell, (int) $value);
                        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
                    } else {
                        $sheet->setCellValue($cell, $value === null ? '' : (string) $value);
                    }
                }

                $isValid = ($record['valid'] ?? '') === 'valid';
                if ($isValid) {
                    $validCount++;
                }

                $outOfPeriod = $record['tanggal_mulai'] < $periodFrom || $record['tanggal_mulai'] > $periodTo;
                $rowRange = "A{$rowNum}:{$lastCol}{$rowNum}";

                if (!$isValid && $outOfPeriod) {
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5E6E0');
                } elseif (!$isValid) {
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FBEEE9');
                } elseif ($outOfPeriod) {
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EDE7F6');
                } elseif ($index % 2 === 1) {
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEF3F9');
                }

                $rowNum++;
            }

            $dataEnd = $rowNum - 1;
            $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setRGB('A0AEC0');
            $sheet->getStyle("A{$dataStart}:A{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach ($footers as $footer) {
            $sheet->mergeCells("A{$rowNum}:{$labelEndCol}{$rowNum}");
            $sheet->setCellValue("A{$rowNum}", $footer['label']);

            foreach ($columns as $i => $col) {
                if ($col['footer'] === null) {
                    continue;
                }
                $cell = Coordinate::stringFromColumnIndex($i + 2) . $rowNum;
                $sheet->setCellValue($cell, (float) ($footer['data'][$col['footer']] ?? 0));
                $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
            }

            $footRange = "A{$rowNum}:{$lastCol}{$rowNum}";
            $sheet->getStyle($footRange)->getFont()->setBold(true)->setSize(9);
            $sheet->getStyle($footRange)->getFill()->setFillType(Fill::FILL_SOLID);
            if ($footer['style'] === 'dark') {
                $sheet->getStyle($footRange)->getFill()->getStartColor()->setRGB('2A3A4D');
                $sheet->getStyle($footRange)->getFont()->getColor()->setRGB('FFFFFF');
            } else {
                $sheet->getStyle($footRange)->getFill()->getStartColor()->setRGB('E8F0F9');
                $sheet->getStyle($footRange)->getFont()->getColor()->setRGB('10315A');
            }
            $sheet->getStyle($footRange)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($footRange)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setRGB('A0AEC0');
            $sheet->getRowDimension($rowNum)->setRowHeight(20);
            $rowNum++;
        }

        $rowNum++;
        $sheet->mergeCells("A{$rowNum}:{$lastCol}{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", "Total baris: {$totalRows}  |  Tervalidasi: {$validCount}  |  Belum: " . ($totalRows - $validCount));
        $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('2A3A4D');
        $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F0F9');
        $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        for ($c = 2; $c <= $totalCols; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->freezePane('A' . ($headerRow + 1));

        $fileName = 'Approval_Penjualan_' . $fileSuffix . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function collectRows($tahun, array $months, array $filter = [], $onlyWeek = null)
    {
        $hariIni = now();
        $records = [];
        $seen = [];

        foreach ($months as $bulan) {
            $startDate = CarbonImmutable::create($tahun, $bulan, 1);
            $endOfMonth = $startDate->endOfMonth();
            $startOfWeek = $startDate->startOfWeek();
            $weekNumber = 1;

            while ($startOfWeek->lte($endOfMonth)) {
                if ($onlyWeek === null || $onlyWeek === $weekNumber) {
                    $start = $startOfWeek->format('Y-m-d');
                    $end = $startOfWeek->endOfWeek()->format('Y-m-d');

                    $rows = Invoice::with([
                        'rkm',
                        'rkm.materi',
                        'rkm.perusahaan',
                        'rkm.sales',
                        'rkm.instruktur',
                        'rkm.outstanding',
                        'rkm.eksam',
                        'rkm.outstanding.tracking_outstanding',
                    ])
                        ->whereHas('rkm', function ($query) use ($start, $end, $hariIni) {
                            $query->whereBetween('tanggal_awal', [$start, $end])
                                ->whereDate('tanggal_awal', '<=', $hariIni);
                        })
                        ->get();

                    $dataValid = ApprovalPendapatan::with(['dataMateri', 'dataPerusahaan'])
                        ->whereIn('id_rkm', $rows->pluck('id_rkm'))
                        ->get()
                        ->keyBy('id_rkm');

                    foreach ($rows as $item) {
                        $rkm = $item->rkm;
                        if (!$rkm) {
                            continue;
                        }

                        $uniqueKey = $item->getKey();
                        if (isset($seen[$uniqueKey])) {
                            continue;
                        }

                        $record = $this->buildExportRecord($item, $rkm, $dataValid->get($item->id_rkm));

                        if (!$this->passesExportFilter($record, $filter)) {
                            continue;
                        }

                        $seen[$uniqueKey] = true;
                        $records[] = $record;
                    }
                }

                $startOfWeek = $startOfWeek->addWeek();
                $weekNumber++;
            }
        }

        return $records;
    }

    private function buildExportRecord($item, $rkm, $valid)
    {
        $hargaNet = (float) ($valid?->harga_net ?? ($item->harga_jual ?? 0));
        $pax = (int) ($valid?->pax ?? ($item->pax ?? 0));
        $total = (float) ($valid?->total_penjualan_kotor ?? $hargaNet * $pax);

        return [
            'id_rkm' => $rkm->id,
            'no_faktur' => $valid?->no_faktur ?? 'belum ada',
            'no_invoice' => $valid?->no_invoice ?? 'belum ada',
            'materi' => $valid?->dataMateri?->nama_materi ?? ($rkm->materi?->nama_materi ?? 'materi kosong'),
            'tanggal_training' => Carbon::parse($rkm->tanggal_awal)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d F Y'),
            'perusahaan' => $valid?->dataPerusahaan?->nama_perusahaan ?? ($rkm->perusahaan?->nama_perusahaan ?? 'perusahaan kosong'),
            'nama_sales' => $rkm->sales?->nama_lengkap ?? 'sales kosong',
            'instruktur' => $rkm->instruktur?->nama_lengkap ?? 'instruktur kosong',
            'harga' => $hargaNet,
            'pax' => $pax,
            'total_penjualan_kotor' => $total,
            'diskon' => (float) ($valid?->diskon ?? 0),
            'total_diskon' => (float) ($valid?->total_diskon ?? 0),
            'total_pa' => (float) ($valid?->total_pa ?? 0),
            'total_cashback' => (float) ($valid?->total_cashback ?? 0),
            'total_uang_saku' => (float) ($valid?->total_uang_saku ?? 0),
            'total_akomodasi' => (float) ($valid?->total_akomodasi ?? 0),
            'oleh_oleh' => (float) ($valid?->oleh_oleh ?? 0),
            'biaya_lain_lain' => (float) ($valid?->biaya_lain_lain ?? 0),
            'entertainment' => (float) ($valid?->entertainment ?? 0),
            'jenis_transport' => $valid?->jenis_transport ?? '-',
            'biaya_transport' => (float) ($valid?->biaya_transport ?? 0),
            'pengurangan_pph' => (float) ($valid?->pengurangan_pph ?? 0),
            'exam_value' => (float) ($valid?->exam ?? $rkm->eksam?->total ?? 0),
            'total_penjualan_sales' => (float) ($valid?->total_penjualan_bersih ?? 0),
            'PPN' => (float) ($valid?->PPN ?? 0),
            'PPH' => (float) ($valid?->PPH ?? 0),
            'jumlah_pembayaran' => (float) ($valid?->jumlah_pembayaran ?? 0),
            'tanggal_pembayaran' => $valid?->tanggal_pembayaran
                ? Carbon::parse($valid->tanggal_pembayaran)->format('Y-m-d')
                : null,
            'biaya_admin' => (float) ($valid?->biaya_admin ?? 0),
            'total_piutang' => (float) ($rkm->outstanding?->net_sales ?? 0),
            'tanggal_mulai' => $valid?->tanggal_mulai
                ? Carbon::parse($valid->tanggal_mulai)->format('Y-m-d')
                : Carbon::parse($rkm->tanggal_awal)->format('Y-m-d'),
            'tanggal_selesai' => $valid?->tanggal_selesai
                ? Carbon::parse($valid->tanggal_selesai)->format('Y-m-d')
                : Carbon::parse($rkm->tanggal_akhir)->format('Y-m-d'),
            'valid' => $valid?->status ?? 'belum tervalidasi',
        ];
    }

    private function passesExportFilter(array $record, array $filter)
    {
        if (!empty($filter['sales']) && !str_contains(strtolower($record['nama_sales']), strtolower($filter['sales']))) {
            return false;
        }
        if (!empty($filter['instruktur']) && !str_contains(strtolower($record['instruktur']), strtolower($filter['instruktur']))) {
            return false;
        }
        if (!empty($filter['status']) && $record['valid'] !== $filter['status']) {
            return false;
        }
        if (!empty($filter['dateStart']) && $record['tanggal_mulai'] < $filter['dateStart']) {
            return false;
        }
        if (!empty($filter['dateEnd']) && $record['tanggal_selesai'] > $filter['dateEnd']) {
            return false;
        }

        $ranges = [
            ['rangeTotal', 'total_penjualan_kotor'],
            ['rangeNett', 'total_penjualan_sales'],
            ['rangePax', 'pax'],
        ];

        foreach ($ranges as [$filterKey, $recordKey]) {
            $range = $filter[$filterKey] ?? [];
            $min = $range['min'] ?? null;
            $max = $range['max'] ?? null;

            if ($min !== null && $min !== '' && $record[$recordKey] < (float) $min) {
                return false;
            }
            if ($max !== null && $max !== '' && $record[$recordKey] > (float) $max) {
                return false;
            }
        }

        return true;
    }

    private function footerTotals($from, $to)
    {
        $base = ApprovalPendapatan::whereDate('tanggal_mulai', '>=', $from)
            ->whereDate('tanggal_mulai', '<=', $to)
            ->where('status', 'valid');

        $row = (clone $base)->selectRaw("
            SUM(CAST(total_penjualan_kotor AS UNSIGNED)) as total_penjualan,
            SUM(CAST(total_diskon AS UNSIGNED)) as total_diskon,
            SUM(CAST(total_pa AS UNSIGNED)) as total_pa,
            SUM(CAST(total_cashback AS UNSIGNED)) as total_cashback,
            SUM(CAST(total_uang_saku AS UNSIGNED)) as total_uang_saku,
            SUM(CAST(total_akomodasi AS UNSIGNED)) as total_akomodasi,
            SUM(CAST(oleh_oleh AS UNSIGNED)) as oleh_oleh,
            SUM(CAST(biaya_lain_lain AS UNSIGNED)) as biaya_lain_lain,
            SUM(CAST(entertainment AS UNSIGNED)) as entertainment,
            SUM(CAST(total_penjualan_bersih AS UNSIGNED)) as total_penjualan_sales,
            SUM(CAST(PPN AS UNSIGNED)) as total_ppn,
            SUM(CAST(PPH AS UNSIGNED)) as total_pph,
            SUM(CAST(pengurangan_pph AS UNSIGNED)) as pengurangan_pph,
            SUM(CAST(jumlah_pembayaran AS UNSIGNED)) as jumlah_pembayaran,
            SUM(CAST(biaya_admin AS UNSIGNED)) as biaya_admin,
            SUM(CAST(biaya_transport AS UNSIGNED)) as biaya_transport,
            SUM(CAST(harga_net AS UNSIGNED)) as total_harga,
            SUM(CAST(pax AS UNSIGNED)) as total_pax,
            SUM(CAST(diskon AS UNSIGNED)) as total_diskon_pa
        ")->first();

        $keys = [
            'total_penjualan', 'total_diskon', 'total_pa', 'total_cashback', 'total_uang_saku',
            'total_akomodasi', 'oleh_oleh', 'biaya_lain_lain', 'entertainment', 'total_penjualan_sales',
            'total_ppn', 'total_pph', 'pengurangan_pph', 'jumlah_pembayaran', 'biaya_admin',
            'biaya_transport', 'total_harga', 'total_pax', 'total_diskon_pa',
        ];

        $totals = [];
        foreach ($keys as $key) {
            $totals[$key] = (float) ($row->{$key} ?? 0);
        }

        $totals['total_exam'] = (float) (clone $base)->sum('exam');
        $totals['total_piutang'] = (float) outstanding::whereIn(
            'id_rkm',
            (clone $base)->pluck('id_rkm')
        )->sum('net_sales');

        return $totals;
    }
}