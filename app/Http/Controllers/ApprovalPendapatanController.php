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

        // Footer Bulanan
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

        // Footer Tahunan
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

            Outstanding::updateOrCreate(
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

        $tahun = (int) ($payload['tahun'] ?? now()->year);
        $bulan = (int) ($payload['bulan'] ?? now()->month);
        $filter = $payload['filter'] ?? [];

        $weeks = $this->getFilteredDataByWeek($tahun, $bulan, $filter);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Approval Penjualan');

        $bulanNama = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $lastCol = 'AH';

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'PT. INIXINDO AMIETE MANDIRI BANDUNG');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('2A3A4D');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'LAPORAN APPROVAL PENJUALAN');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('5E8BC2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'Periode: ' . $bulanNama[$bulan - 1] . ' ' . $tahun);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('6B7C93');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'No',
            'No Faktur',
            'No Invoice',
            'Materi',
            'Tanggal Training',
            'Perusahaan',
            'Sales',
            'Instruktur',
            'Harga',
            'Pax',
            'Total Penjualan Kotor',
            'Diskon/PA',
            'Total Diskon',
            'Total PA',
            'Cashback',
            'Uang Saku',
            'Akomodasi',
            'Oleh-Oleh Peserta',
            'Biaya Lain-Lain',
            'Entertainment',
            'Jenis Transport',
            'Biaya Transport',
            'Pengurangan PPH',
            'Exam',
            'Total Penjualan Sales (Bersih)',
            'PPN',
            'PPH',
            'Jumlah Pembayaran',
            'Tanggal Pembayaran',
            'Biaya Admin',
            'Total Piutang',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Status',
        ];

        $currencyCols = ['I', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AD', 'AE'];

        $rowNum = 5;
        $grandTotalRows = 0;
        $grandValid = 0;

        foreach ($weeks as $week) {
            $weekItems = $week['data'] ?? [];
            $weekNumber = $week['week_number'] ?? 0;
            $startLabel = $week['start_label'] ?? ($week['start'] ?? '');
            $endLabel = $week['end_label'] ?? ($week['end'] ?? '');
            $validCount = collect($weekItems)->where('valid', 'valid')->count();
            $totalRows = count($weekItems);

            // --- Judul minggu ---
            $sheet->mergeCells("A{$rowNum}:{$lastCol}{$rowNum}");
            $titleWeek = "Minggu {$weekNumber}  ·  Periode: {$startLabel} – {$endLabel}";
            if ($totalRows > 0) {
                $titleWeek .= "  ·  {$validCount}/{$totalRows} tervalidasi";
            } else {
                $titleWeek .= '  ·  Tidak ada data';
            }
            $sheet->setCellValue("A{$rowNum}", $titleWeek);
            $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('5E8BC2');
            $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension($rowNum)->setRowHeight(22);
            $rowNum++;

            $headerRow = $rowNum;
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . $headerRow, $h);
                $col++;
            }
            $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
            $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('7AA4D4');
            $sheet->getStyle($headerRange)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setRGB('5E8BC2');
            $sheet->getRowDimension($headerRow)->setRowHeight(28);
            $rowNum++;

            $dataStart = $rowNum;

            if ($totalRows === 0) {
                $sheet->mergeCells("A{$rowNum}:{$lastCol}{$rowNum}");
                $sheet->setCellValue("A{$rowNum}", 'Tidak ada data dalam periode minggu ini');
                $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('6B7C93');
                $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F7F9FC');
                $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('E4E9F1');
                $rowNum++;
            } else {
                $no = 1;
                foreach ($weekItems as $item) {
                    $sheet->setCellValue('A' . $rowNum, $no++);
                    $sheet->setCellValue('B' . $rowNum, $item['no_faktur'] ?? '');
                    $sheet->setCellValue('C' . $rowNum, $item['no_invoice'] ?? '');
                    $sheet->setCellValue('D' . $rowNum, $item['materi'] ?? '');
                    $sheet->setCellValue('E' . $rowNum, $item['tanggal_training'] ?? '');
                    $sheet->setCellValue('F' . $rowNum, $item['perusahaan'] ?? '');
                    $sheet->setCellValue('G' . $rowNum, $item['nama_sales'] ?? '');
                    $sheet->setCellValue('H' . $rowNum, $item['instruktur'] ?? '');
                    $sheet->setCellValue('I' . $rowNum, (float) ($item['harga'] ?? 0));
                    $sheet->setCellValue('J' . $rowNum, (int) ($item['pax'] ?? 0));
                    $sheet->setCellValue('K' . $rowNum, (float) ($item['total_penjualan_kotor'] ?? 0));
                    $sheet->setCellValue('L' . $rowNum, (float) ($item['diskon'] ?? 0));
                    $sheet->setCellValue('M' . $rowNum, (float) ($item['total_diskon'] ?? 0));
                    $sheet->setCellValue('N' . $rowNum, (float) ($item['total_pa'] ?? 0));
                    $sheet->setCellValue('O' . $rowNum, (float) ($item['total_cashback'] ?? 0));
                    $sheet->setCellValue('P' . $rowNum, (float) ($item['total_uang_saku'] ?? 0));
                    $sheet->setCellValue('Q' . $rowNum, (float) ($item['total_akomodasi'] ?? 0));
                    $sheet->setCellValue('R' . $rowNum, (float) ($item['oleh_oleh'] ?? 0));
                    $sheet->setCellValue('S' . $rowNum, (float) ($item['biaya_lain_lain'] ?? 0));
                    $sheet->setCellValue('T' . $rowNum, (float) ($item['entertainment'] ?? 0));
                    $sheet->setCellValue('U' . $rowNum, $item['jenis_transport'] ?? '');
                    $sheet->setCellValue('V' . $rowNum, (float) ($item['biaya_transport'] ?? 0));
                    $sheet->setCellValue('W' . $rowNum, (float) ($item['pengurangan_pph'] ?? 0));
                    $sheet->setCellValue('X' . $rowNum, (float) ($item['exam_value'] ?? 0));
                    $sheet->setCellValue('Y' . $rowNum, (float) ($item['total_penjualan_sales'] ?? 0));
                    $sheet->setCellValue('Z' . $rowNum, (float) ($item['PPN'] ?? 0));
                    $sheet->setCellValue('AA' . $rowNum, (float) ($item['PPH'] ?? 0));
                    $sheet->setCellValue('AB' . $rowNum, (float) ($item['jumlah_pembayaran'] ?? 0));
                    $sheet->setCellValue('AC' . $rowNum, $item['tanggal_pembayaran'] ?? '');
                    $sheet->setCellValue('AD' . $rowNum, (float) ($item['biaya_admin'] ?? 0));
                    $sheet->setCellValue('AE' . $rowNum, (float) ($item['total_piutang'] ?? 0));
                    $sheet->setCellValue('AF' . $rowNum, $item['tanggal_mulai'] ?? '');
                    $sheet->setCellValue('AG' . $rowNum, $item['tanggal_selesai'] ?? '');
                    $sheet->setCellValue('AH' . $rowNum, $item['valid'] ?? '');

                    foreach ($currencyCols as $c) {
                        $sheet->getStyle($c . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
                    }

                    // Zebra
                    if ($no % 2 === 0) {
                        $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('EEF3F9');
                    }

                    // Highlight belum valid
                    if (($item['valid'] ?? '') !== 'valid') {
                        $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('FBEEE9');
                    }

                    $rowNum++;
                }

                $dataEnd = $rowNum - 1;
                $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('A0AEC0');

                $grandTotalRows += $totalRows;
                $grandValid += $validCount;
            }

            // Spasi antar minggu
            $rowNum++;
        }

        // ===== Ringkasan bawah =====
        $sheet->mergeCells("A{$rowNum}:{$lastCol}{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", "Total keseluruhan: {$grandTotalRows} baris  |  Tervalidasi: {$grandValid}  |  Belum: " . ($grandTotalRows - $grandValid));
        $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('2A3A4D');
        $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E8F0F9');
        $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Auto-size
        foreach (range('A', 'Z') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        foreach (['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH'] as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $sheet->freezePane('A5');

        $fileName = 'Approval_Penjualan_' . $bulanNama[$bulan - 1] . '_' . $tahun . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function getFilteredDataByWeek($tahun, $bulan, $filter = [])
    {
        $startDate = CarbonImmutable::create($tahun, $bulan, 1);
        $endDate = CarbonImmutable::create($tahun, $bulan, 1)->endOfMonth();
        $hariIni = now();

        $startOfWeek = $startDate->copy()->startOfWeek();
        $endOfMonth = $endDate->copy();

        $weekNumber = 1;
        $weeks = [];

        while ($startOfWeek->lte($endOfMonth)) {
            $endOfWeek = $startOfWeek->copy()->endOfWeek();
            $start = $startOfWeek->format('Y-m-d');
            $end = $endOfWeek->format('Y-m-d');

            $startLabel = Carbon::parse($start)->translatedFormat('d F Y');
            $endLabel = Carbon::parse($start)->addDays(4)->translatedFormat('d F Y');

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

            $weekData = [];

            foreach ($rows as $item) {
                $rkm = $item->rkm;
                if (!$rkm) {
                    continue;
                }

                $valid = $dataValid->get($item->id_rkm);
                $hargaNet = (float) ($valid?->harga_net ?? ($item->harga_jual ?? 0));
                $pax = (int) ($valid?->pax ?? ($item->pax ?? 0));
                $total = (float) ($valid?->total_penjualan_kotor ?? $hargaNet * $pax);

                $record = [
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

                if (!empty($filter['sales']) && !str_contains(strtolower($record['nama_sales']), strtolower($filter['sales']))) {
                    continue;
                }
                if (!empty($filter['status']) && $record['valid'] !== $filter['status']) {
                    continue;
                }
                if (!empty($filter['dateStart']) && $record['tanggal_mulai'] < $filter['dateStart']) {
                    continue;
                }
                if (!empty($filter['dateEnd']) && $record['tanggal_selesai'] > $filter['dateEnd']) {
                    continue;
                }

                $rangeTotal = $filter['rangeTotal'] ?? [];
                if (isset($rangeTotal['min']) && $rangeTotal['min'] !== null && $rangeTotal['min'] !== '' && $record['total_penjualan_kotor'] < (float) $rangeTotal['min']) {
                    continue;
                }
                if (isset($rangeTotal['max']) && $rangeTotal['max'] !== null && $rangeTotal['max'] !== '' && $record['total_penjualan_kotor'] > (float) $rangeTotal['max']) {
                    continue;
                }

                $rangeNett = $filter['rangeNett'] ?? [];
                if (isset($rangeNett['min']) && $rangeNett['min'] !== null && $rangeNett['min'] !== '' && $record['total_penjualan_sales'] < (float) $rangeNett['min']) {
                    continue;
                }
                if (isset($rangeNett['max']) && $rangeNett['max'] !== null && $rangeNett['max'] !== '' && $record['total_penjualan_sales'] > (float) $rangeNett['max']) {
                    continue;
                }

                $rangePax = $filter['rangePax'] ?? [];
                if (isset($rangePax['min']) && $rangePax['min'] !== null && $rangePax['min'] !== '' && $record['pax'] < (int) $rangePax['min']) {
                    continue;
                }
                if (isset($rangePax['max']) && $rangePax['max'] !== null && $rangePax['max'] !== '' && $record['pax'] > (int) $rangePax['max']) {
                    continue;
                }

                $weekData[] = $record;
            }

            $weeks[] = [
                'week_number' => $weekNumber,
                'start' => $start,
                'end' => $end,
                'start_label' => $startLabel,
                'end_label' => $endLabel,
                'data' => $weekData,
            ];

            $startOfWeek = $startOfWeek->addWeek();
            $weekNumber++;
        }

        return $weeks;
    }
}