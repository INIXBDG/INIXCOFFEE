<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\karyawan;
use App\Models\PengajuanBarang;
use App\Models\detailPengajuanBarang;
use App\Models\tracking_pengajuan_barang;
use App\Models\User;
use App\Models\RKM;
use App\Models\Materi;
use App\Models\Subscription;
use App\Models\PengajuanSubs;
use App\Models\TrackingPengajuanSubs;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\PengajuanLabdanSubsNotification;
use App\Notifications\ApprovalLabSubsNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PengajuanSubsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user || !$user->karyawan) {
            return view('auth.login');
        }

        $tracking = 'buka';
        $materis  = Materi::orderBy('nama_materi', 'asc')->get();

        // Daftar seluruh karyawan untuk fitur Assign Pengguna Subscription oleh Tim ITSM
        $karyawans = karyawan::orderBy('nama_lengkap', 'asc')->get();

        return view('pengajuansubs.index', compact('tracking', 'materis', 'karyawans'));
    }

    public function getPengajuanSubs($month, $year)
    {
        $user     = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $jabatan  = $karyawan->jabatan;
        $divisi   = $karyawan->divisi;

        $relations = ['karyawan', 'tracking', 'subs', 'rkm.perusahaan', 'rkm.materi'];

        if (in_array($jabatan, ['Finance & Accounting', 'Koordinator ITSM', 'GM', 'Technical Support', 'Education Manager'])) {
            $Pengajuan = PengajuanSubs::with($relations)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->latest()
                ->get();
        } elseif (in_array($jabatan, ['Office Manager', 'SPV Sales'])) {
            $Pengajuan = PengajuanSubs::with($relations)
                ->whereHas('karyawan', function ($q) use ($divisi) {
                    $q->where('divisi', $divisi);
                })
                ->latest()
                ->get();
        } else {
            $Pengajuan = PengajuanSubs::with($relations)
                ->where('kode_karyawan', $karyawan->kode_karyawan)
                ->latest()
                ->get();
        }

        $Pengajuan->transform(function ($item) {
            $pengajuanBarang = PengajuanBarang::where('tipe', 'Subscription')
                ->where('id_kegiatan', $item->id)
                ->first();

            if ($pengajuanBarang) {
                $trackingBarang = tracking_pengajuan_barang::where('id_pengajuan_barang', $pengajuanBarang->id)->get();
                $mergedTracking = $item->tracking->concat($trackingBarang)->sortBy('tanggal')->values();
                $item->setRelation('tracking', $mergedTracking);
            }

            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'List Pengajuan Subscription',
            'data'    => $Pengajuan,
        ]);
    }

    public function getMasterSubs()
    {
        $subs = Subscription::with(['materis', 'pengajuans.karyawan', 'pengajuans.tracking'])
            ->orderBy('created_at', 'desc')
            ->get();

        $subs->transform(function ($sub) {
            $activeUsers = $sub->pengajuans->filter(function ($p) use ($sub) {
                $tracks      = $p->tracking->pluck('tracking')->implode(' ');
                $tracksLower = strtolower($tracks);

                $hasAccess    = str_contains($tracksLower, 'lihat akses nya di detail') || str_contains($tracksLower, 'akses subscription siap digunakan') || str_contains($tracksLower, 'siap digunakan');
                $isUnassigned = str_contains($tracksLower, 'di-unassign') || str_contains($tracksLower, 'akses dicabut');

                if (!$hasAccess || $isUnassigned) {
                    return false;
                }

                if ($sub->end_date) {
                    return now()->startOfDay()->lte($sub->end_date);
                }

                return $sub->status === 'active';
            })->map(function ($p) use ($sub) {
                return [
                    'pengajuan_id' => $p->id,
                    'nama'         => $p->karyawan->nama_lengkap ?? '-',
                    'jabatan'      => $p->karyawan->jabatan ?? '-',
                    'divisi'       => $p->karyawan->divisi ?? '-',
                    'periode'      => ($sub->start_date && $sub->end_date)
                        ? Carbon::parse($sub->start_date)->format('d/m/y') . ' - ' . Carbon::parse($sub->end_date)->format('d/m/y')
                        : ($sub->end_date ? 'Exp: ' . Carbon::parse($sub->end_date)->format('d/m/y') : 'Mengikuti Masa Aktif Subs'),
                ];
            })->values();

            // Ekstraksi identitas/email akun dari desc terstruktur "[Akun: ...]" atau dari access_code
            $akunIdentitas = null;
            $cleanDesc     = $sub->desc;
            if (!empty($sub->desc) && preg_match('/^\[Akun:\s*(.*?)\]\s*(.*)$/s', $sub->desc, $matches)) {
                $akunIdentitas = trim($matches[1]);
                $cleanDesc     = trim($matches[2]);
            } elseif (!empty($sub->access_code) && filter_var(trim(explode('/', explode('|', $sub->access_code)[0])[0]), FILTER_VALIDATE_EMAIL)) {
                $akunIdentitas = trim(explode('/', explode('|', $sub->access_code)[0])[0]);
            }

            $sub->active_users   = $activeUsers;
            $sub->akun_identitas = $akunIdentitas;
            $sub->clean_desc     = $cleanDesc;
            return $sub;
        });

        return response()->json([
            'success' => true,
            'data'    => $subs
        ]);
    }

    /**
     * Tambah Master Subscription yang sudah berjalan (Oleh Koor ITSM / Technical Support)
     */
    public function storeMasterSubs(Request $request)
    {
        $user         = auth()->user();
        $itsmKaryawan = $user->karyawan;

        if (!in_array($itsmKaryawan->jabatan, ['Technical Support', 'Koordinator ITSM'])) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki akses.'], 403);
        }

        $request->validate([
            'nama_subs'              => 'required|string|max:255',
            'akun_identitas'         => 'nullable|string|max:255',
            'merk'                   => 'nullable|string|max:255',
            'tipe'                   => 'required|string|in:subscription,one-time',
            'status'                 => 'required|string|in:active,pending,expired',
            'desc'                   => 'nullable|string',
            'subs_url'               => 'nullable|string|max:255',
            'access_code'            => 'nullable|string|max:255',
            'start_date'             => 'nullable|date',
            'end_date'               => 'nullable|date',
            'mata_uang'              => 'nullable|string|max:50',
            'harga'                  => 'nullable|numeric',
            'kurs'                   => 'nullable|numeric',
            'harga_rupiah'           => 'nullable|numeric',
            'materi_ids'             => 'nullable|array',
            'materi_ids.*'           => 'exists:materis,id',
            'assigned_kode_karyawan' => 'nullable|array',
        ]);

        $mataUang = $request->mata_uang ?? 'Rupiah';
        $harga    = $request->harga ?? 0;
        $kurs     = ($mataUang === 'Rupiah') ? 1 : ($request->kurs ?? 1);
        $hargaRupiah = $request->filled('harga_rupiah') ? $request->harga_rupiah : ($harga * $kurs);
        if ($mataUang === 'Rupiah') {
            $hargaRupiah = $harga;
        }

        $formattedDesc = $request->desc;
        if ($request->filled('akun_identitas')) {
            $identitas     = trim($request->akun_identitas);
            $formattedDesc = "[Akun: {$identitas}]" . ($formattedDesc ? ' ' . trim($formattedDesc) : '');
        }

        $subs = Subscription::create([
            'kode_karyawan' => $itsmKaryawan->kode_karyawan,
            'nama_subs'     => $request->nama_subs,
            'merk'          => $request->merk,
            'tipe'          => $request->tipe,
            'desc'          => $formattedDesc,
            'subs_url'      => $request->subs_url,
            'access_code'   => $request->access_code,
            'status'        => $request->status,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'mata_uang'     => $mataUang,
            'harga'         => $harga,
            'kurs'          => $kurs,
            'harga_rupiah'  => $hargaRupiah,
            'is_active'     => ($request->status === 'active'),
        ]);

        if ($request->filled('materi_ids')) {
            $subs->materis()->sync($request->materi_ids);
        }

        if ($request->filled('assigned_kode_karyawan') && is_array($request->assigned_kode_karyawan)) {
            foreach ($request->assigned_kode_karyawan as $kodeKaryawan) {
                $this->createDirectAccessAssignment($subs, $kodeKaryawan, $itsmKaryawan);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Subscription berhasil ditambahkan ke Master Subscription!',
            'data'    => $subs
        ]);
    }

    /**
     * Assign Karyawan yang sedang menggunakan Subscription dari Management Master Subscription
     */
    public function assignSubsUser(Request $request, $id)
    {
        $user         = auth()->user();
        $itsmKaryawan = $user->karyawan;

        if (!in_array($itsmKaryawan->jabatan, ['Technical Support', 'Koordinator ITSM'])) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki akses.'], 403);
        }

        $request->validate([
            'kode_karyawan_list'   => 'required|array|min:1',
            'kode_karyawan_list.*' => 'required|exists:karyawans,kode_karyawan',
            'catatan_assign'       => 'nullable|string|max:500',
        ]);

        $subs = Subscription::findOrFail($id);

        if ($subs->status !== 'active') {
            $subs->update(['status' => 'active', 'is_active' => true]);
        }

        foreach ($request->kode_karyawan_list as $kodeKaryawan) {
            $this->createDirectAccessAssignment($subs, $kodeKaryawan, $itsmKaryawan, $request->catatan_assign);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil di-assign dan akses subscription telah dicatat ke dalam Tracking Penggunaan!'
        ]);
    }

    /**
     * Unassign / Cabut Akses Pengguna Subscription
     */
    public function unassignSubsUser($pengajuanId)
    {
        $user         = auth()->user();
        $itsmKaryawan = $user->karyawan;

        if (!in_array($itsmKaryawan->jabatan, ['Technical Support', 'Koordinator ITSM'])) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki akses.'], 403);
        }

        $pengajuan = PengajuanSubs::with('karyawan', 'subs')->findOrFail($pengajuanId);
        $namaSubs  = $pengajuan->subs->nama_subs ?? ($pengajuan->subs_snapshot['nama_subs'] ?? 'Subscription');

        $statusText = "Selesai - Akses dicabut (Di-unassign) oleh {$itsmKaryawan->nama_lengkap} ({$itsmKaryawan->jabatan})";

        $track = TrackingPengajuanSubs::create([
            'id_pengajuan_subs' => $pengajuan->id,
            'tracking'          => $statusText,
            'tanggal'           => now(),
        ]);

        $pengajuan->update(['id_tracking' => $track->id]);

        $notifData = [
            'tanggal' => now(),
            'status'  => "Selesai - Akses Subscription ({$namaSubs}) telah dicabut (Di-unassign) oleh {$itsmKaryawan->nama_lengkap} ({$itsmKaryawan->jabatan})",
        ];

        $targetUsers = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $pengajuan->kode_karyawan))->get();
        foreach ($targetUsers as $u) {
            NotificationFacade::send(
                $u,
                new ApprovalLabSubsNotification(
                    $notifData,
                    '/pengajuansubs/' . $pengajuan->id,
                    $pengajuan->karyawan->nama_lengkap ?? 'Pengguna',
                    'Unassign Akses Lab/Subscription',
                    $u->id
                )
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Akses subscription untuk ' . ($pengajuan->karyawan->nama_lengkap ?? 'pengguna') . ' berhasil di-unassign!'
        ]);
    }

    /**
     * Helper internal untuk mencatat assignment pengguna langsung sebagai Permintaan Akses yang Disetujui
     */
    private function createDirectSubsAssignment(Subscription $subs, string $kodeKaryawan, $itsmKaryawan, $catatan = null)
    {
        $targetKaryawan = karyawan::where('kode_karyawan', $kodeKaryawan)->first();
        if (!$targetKaryawan) {
            return null;
        }

        $snapshot = $subs->toArray();
        $snapshot['handled_by']         = $itsmKaryawan->nama_lengkap;
        $snapshot['handled_by_jabatan'] = $itsmKaryawan->jabatan;
        $snapshot['requested_qty']      = 1;
        $snapshot['qty']                = 1;
        $snapshot['requested_catatan']  = $catatan ?? 'Di-assign langsung oleh Tim ITSM (' . $itsmKaryawan->nama_lengkap . ')';

        $pengajuan = PengajuanSubs::create([
            'kode_karyawan'   => $targetKaryawan->kode_karyawan,
            'id_subs'         => $subs->id,
            'id_rkm'          => null,
            'jenis_transaksi' => 'existing',
            'subs_snapshot'   => $snapshot,
        ]);

        $statusText = "Telah disetujui oleh Koordinator ITSM dan lihat akses nya di Detail (Di-assign oleh {$itsmKaryawan->nama_lengkap})";

        TrackingPengajuanSubs::create([
            'id_pengajuan_subs' => $pengajuan->id,
            'tracking'          => $statusText,
            'tanggal'           => now(),
        ]);

        $finalTrack = TrackingPengajuanSubs::create([
            'id_pengajuan_subs' => $pengajuan->id,
            'tracking'          => 'Selesai - Akses Subscription Siap Digunakan',
            'tanggal'           => now()->addSeconds(1),
        ]);

        $pengajuan->update(['id_tracking' => $finalTrack->id]);

        $notifData = [
            'tanggal' => now(),
            'status'  => "Telah disetujui oleh Koordinator ITSM dan lihat akses nya di Detail (Subscription: {$subs->nama_subs} - Di-assign oleh {$itsmKaryawan->nama_lengkap})",
        ];

        $targetUsers = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $targetKaryawan->kode_karyawan))->get();
        foreach ($targetUsers as $u) {
            NotificationFacade::send(
                $u,
                new ApprovalLabSubsNotification(
                    $notifData,
                    '/pengajuansubs/' . $pengajuan->id,
                    $targetKaryawan->nama_lengkap,
                    'Assign Akses Lab/Subscription',
                    $u->id
                )
            );
        }

        return $pengajuan;
    }

    private function createDirectAccessAssignment(Subscription $subs, string $kodeKaryawan, $itsmKaryawan, $catatan = null)
    {
        return $this->createDirectSubsAssignment($subs, $kodeKaryawan, $itsmKaryawan, $catatan);
    }

    public function updateMasterSubs(Request $request, $id)
    {
        $request->validate([
            'nama_subs'      => 'required|string|max:255',
            'akun_identitas' => 'nullable|string|max:255',
            'merk'           => 'nullable|string|max:255',
            'tipe'           => 'nullable|in:subscription,one-time',
            'status'         => 'nullable|in:active,pending,expired',
            'mata_uang'      => 'nullable|string',
            'harga'          => 'nullable|numeric',
            'kurs'           => 'nullable|numeric',
            'harga_rupiah'   => 'nullable|numeric',
            'materi_ids'     => 'nullable|array',
            'materi_ids.*'   => 'exists:materis,id'
        ]);

        $subs = Subscription::findOrFail($id);

        $mataUang = $request->mata_uang ?? $subs->mata_uang ?? 'Rupiah';
        $harga    = $request->harga ?? $subs->harga ?? 0;
        $kurs     = ($mataUang === 'Rupiah') ? 1 : ($request->kurs ?? $subs->kurs ?? 1);
        $hargaRupiah = $request->filled('harga_rupiah') ? $request->harga_rupiah : ($harga * $kurs);
        if ($mataUang === 'Rupiah') {
            $hargaRupiah = $harga;
        }

        $rawDesc = $request->desc ?? $request->deskripsi ?? '';
        // Bersihkan prefix [Akun: ...] lama jika ada agar tidak duplikat
        $rawDesc = preg_replace('/^\[Akun:\s*.*?\]\s*/s', '', $rawDesc);
        if ($request->filled('akun_identitas')) {
            $identitas = trim($request->akun_identitas);
            $rawDesc   = "[Akun: {$identitas}]" . ($rawDesc ? ' ' . trim($rawDesc) : '');
        }

        $subs->update([
            'nama_subs'    => $request->nama_subs,
            'merk'         => $request->merk,
            'tipe'         => $request->tipe,
            'status'       => $request->status,
            'is_active'    => ($request->status === 'active'),
            'desc'         => $rawDesc,
            'subs_url'     => $request->subs_url ?? $request->url_subs,
            'access_code'  => $request->access_code ?? $request->kode_akses,
            'start_date'   => $request->start_date ?? $request->tanggal_mulai,
            'end_date'     => $request->end_date ?? $request->tanggal_berakhir,
            'mata_uang'    => $mataUang,
            'harga'        => $harga,
            'kurs'         => $kurs,
            'harga_rupiah' => $hargaRupiah,
        ]);

        if ($request->has('materi_ids')) {
            $subs->materis()->sync($request->materi_ids);
        } else {
            $subs->materis()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Data subscription berhasil diperbarui',
            'data'    => $subs
        ]);
    }

    public function create()
    {
        $user     = auth()->user();
        $karyawan = $user->karyawan;

        $masterSubs = Subscription::orderBy('nama_subs', 'asc')->get();

        return view('pengajuansubs.create', compact('karyawan', 'masterSubs'));
    }

    public function getSubsByRkm($rkmId)
    {
        $rkm = RKM::with('materi')->find($rkmId);

        if (!$rkm || !$rkm->materi) {
            return response()->json(['materi_nama' => '', 'subs' => []]);
        }

        $subs = $rkm->materi->subscriptions()->get();

        return response()->json([
            'materi_nama' => $rkm->materi->nama_materi,
            'subs'        => $subs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_existing_subs' => 'nullable|exists:subscriptions,id',
            'nama_subs'        => 'required|string|max:255',
            'merk'             => 'required|string|max:255',
            'qty'              => 'required|integer|min:1',
            'catatan'          => 'nullable|string|max:500',
        ], [
            'nama_subs.required' => 'Nama Subscription / Software wajib diisi.',
            'merk.required'      => 'Vendor / Merk wajib diisi.',
            'qty.required'       => 'Jumlah akun / lisensi wajib diisi.',
            'qty.min'            => 'Jumlah akun minimal 1.',
        ]);

        $karyawan = karyawan::where('kode_karyawan', $request->kode_karyawan)->firstOrFail();
        $qty      = (int) $request->qty;

        if ($request->filled('id_existing_subs')) {
            $subsId = $request->id_existing_subs;
        } else {
            $existingSubs = Subscription::whereRaw('LOWER(nama_subs) = ?', [strtolower(trim($request->nama_subs))])
                ->whereRaw('LOWER(merk) = ?', [strtolower(trim($request->merk))])
                ->first();

            if ($existingSubs) {
                $subsId = $existingSubs->id;
            } else {
                $newSubs = Subscription::create([
                    'kode_karyawan' => $karyawan->kode_karyawan,
                    'nama_subs'     => trim($request->nama_subs),
                    'merk'          => trim($request->merk),
                    'tipe'          => 'subscription',
                    'desc'          => $request->catatan ?? ("Permintaan {$qty} Akun Subscription oleh " . $karyawan->nama_lengkap),
                    'subs_url'      => null,
                    'status'        => 'pending',
                    'is_active'     => false,
                ]);
                $subsId = $newSubs->id;
            }
        }

        $jabatan  = $karyawan->jabatan;
        $isAtasan = in_array($jabatan, ['Education Manager', 'SPV Sales', 'GM', 'Koordinator ITSM']);

        if ($isAtasan) {
            $trackingText   = "Diajukan oleh {$jabatan} dan sedang ditinjau oleh Koordinator ITSM";
            $targetJabatans = ['Koordinator ITSM', 'Technical Support'];
            $successMsg     = "Permintaan {$qty} akun subscription berhasil dikirim dan langsung masuk ke antrean Tim ITSM.";
        } else {
            $atasanJabatan = match ($karyawan->divisi) {
                'Education'             => 'Education Manager',
                'Sales & Marketing'     => 'SPV Sales',
                'Office'                => 'GM',
                'IT Service Management' => 'Koordinator ITSM',
                default                 => 'GM',
            };

            $trackingText   = "Diajukan dan Sedang Ditinjau oleh {$atasanJabatan}";
            $targetJabatans = array_unique([$atasanJabatan, 'Koordinator ITSM']);
            $successMsg     = "Permintaan {$qty} akun subscription berhasil dikirim dan menunggu persetujuan {$atasanJabatan}.";
        }

        $pengajuan = PengajuanSubs::create([
            'kode_karyawan'   => $karyawan->kode_karyawan,
            'id_subs'         => $subsId,
            'id_rkm'          => null,
            'jenis_transaksi' => 'existing',
            'subs_snapshot'   => [
                'requested_nama_subs' => trim($request->nama_subs),
                'requested_merk'      => trim($request->merk),
                'requested_qty'       => $qty,
                'qty'                 => $qty,
                'requested_catatan'   => $request->catatan,
            ],
        ]);

        $trackingModel = TrackingPengajuanSubs::create([
            'id_pengajuan_subs' => $pengajuan->id,
            'tracking'          => $trackingText,
            'tanggal'           => now(),
        ]);

        $pengajuan->update(['id_tracking' => $trackingModel->id]);

        $namaSubsClean = trim($request->nama_subs) . ' (' . trim($request->merk) . ')';
        $notifData = [
            'id_karyawan'       => $karyawan->id,
            'tanggal_pengajuan' => now(),
            'jenis_pengajuan'   => 'subscription',
            'nama'              => "{$qty} Akun {$namaSubsClean}",
            'deskripsi'         => "Permintaan {$qty} Akun {$namaSubsClean}" . ($request->catatan ? " - {$request->catatan}" : ''),
            'rkm'               => null,
        ];

        $receivers = User::whereHas('karyawan', function ($q) use ($targetJabatans) {
            $q->whereIn('jabatan', $targetJabatans);
        })->get();

        $path = "/pengajuansubs";
        $type = "Mengajukan Subscription";

        foreach ($receivers as $receiver) {
            NotificationFacade::send(
                $receiver,
                new PengajuanLabdanSubsNotification($notifData, $path, $type, $receiver->id)
            );
        }

        return redirect()->route('pengajuansubs.index')->with('success', $successMsg);
    }

    public function edit($id)
    {
        $user    = auth()->user();
        $jabatan = $user->karyawan->jabatan;

        if (!in_array($jabatan, ['Technical Support', 'Koordinator ITSM'])) {
            return redirect()->route('pengajuansubs.index')
                ->with('error', 'Anda tidak memiliki akses ke halaman Management Subscription.');
        }

        $data       = PengajuanSubs::with(['subs', 'karyawan'])->findOrFail($id);
        $masterSubs = Subscription::with(['pengajuans.karyawan', 'pengajuans.tracking'])
            ->orderBy('nama_subs', 'asc')
            ->get();

        $masterSubs->transform(function ($sub) {
            $activeUsers = $sub->pengajuans->filter(function ($p) use ($sub) {
                $tracks      = $p->tracking->pluck('tracking')->implode(' ');
                $tracksLower = strtolower($tracks);

                $hasAccess    = str_contains($tracksLower, 'lihat akses nya di detail') || str_contains($tracksLower, 'akses subscription siap digunakan') || str_contains($tracksLower, 'siap digunakan');
                $isUnassigned = str_contains($tracksLower, 'di-unassign') || str_contains($tracksLower, 'akses dicabut');

                if (!$hasAccess || $isUnassigned) {
                    return false;
                }

                if ($sub->end_date) {
                    return now()->startOfDay()->lte($sub->end_date);
                }

                return $sub->status === 'active';
            })->values();

            $akunIdentitas = null;
            if (!empty($sub->desc) && preg_match('/^\[Akun:\s*(.*?)\]/s', $sub->desc, $matches)) {
                $akunIdentitas = trim($matches[1]);
            } elseif (!empty($sub->access_code) && filter_var(trim(explode('/', explode('|', $sub->access_code)[0])[0]), FILTER_VALIDATE_EMAIL)) {
                $akunIdentitas = trim(explode('/', explode('|', $sub->access_code)[0])[0]);
            }

            $sub->active_users_count = $activeUsers->count();
            $sub->is_idle            = ($sub->status === 'active' && $sub->active_users_count === 0 && (!$sub->end_date || now()->startOfDay()->lte($sub->end_date)));
            $sub->akun_identitas     = $akunIdentitas;
            return $sub;
        });

        $karyawans = karyawan::orderBy('nama_lengkap', 'asc')->get();

        return view('pengajuansubs.edit', compact('data', 'masterSubs', 'karyawans'));
    }

    public function updateSubsSubs(Request $request, $id)
    {
        $user         = auth()->user();
        $itsmKaryawan = $user->karyawan;

        if (!in_array($itsmKaryawan->jabatan, ['Technical Support', 'Koordinator ITSM'])) {
            return redirect()->route('pengajuansubs.index')
                ->with('error', 'Aksi ini hanya dapat dilakukan oleh Koordinator ITSM atau Technical Support.');
        }

        $data = PengajuanSubs::with(['subs', 'karyawan'])->findOrFail($id);

        $validated = $request->validate([
            'id_subs_selected'          => 'nullable|exists:subscriptions,id',
            'keputusan_itsm'            => 'required|in:berikan_akses,pengajuan_barang_baru,split_existing_dan_baru,pengajuan_barang_perpanjangan',
            'existing_subs_ids'         => 'nullable|array',
            'existing_subs_ids.*'       => 'exists:subscriptions,id',
            'existing_assigned_users'   => 'nullable|array',
            'existing_assigned_users.*' => 'exists:karyawans,kode_karyawan',
            'qty_pengadaan'             => 'nullable|integer|min:1',
            'nama_subs'                 => 'required|string|max:255',
            'merk'                      => 'nullable|string|max:255',
            'tipe'                      => 'required|in:one-time,subscription',
            'qty'                       => 'required|integer|min:1',
            'split_master_subs'         => 'nullable|boolean',
            'desc'                      => 'nullable|string',
            'subs_url'                  => 'nullable|url',
            'access_code'               => 'nullable|string|max:255',
            'mata_uang'                 => 'nullable|string|max:50',
            'harga'                     => 'nullable|numeric',
            'kurs'                      => 'nullable|numeric',
            'harga_rupiah'              => 'nullable|string',
            'start_date'                => 'nullable|date',
            'end_date'                  => 'nullable|date',
            'status'                    => 'required|string|in:active,pending,expired',
        ]);

        $oldSnapshot     = $data->subs_snapshot ?? [];
        $keputusan       = $validated['keputusan_itsm'];
        $qty             = (int) ($validated['qty'] ?? ($oldSnapshot['requested_qty'] ?? 1));
        $hargaSatuanAsli = (float) ($validated['harga'] ?? 0);
        $mataUang        = $validated['mata_uang'] ?? 'Rupiah';
        $kurs            = ($mataUang === 'Rupiah') ? 1 : (float) ($validated['kurs'] ?? 1);

        $hargaSatuanRupiah = (int) round($hargaSatuanAsli * $kurs);
        if ($mataUang === 'Rupiah') {
            $hargaSatuanRupiah = (int) round($hargaSatuanAsli);
        }

        $baseName = preg_replace('/\s*#\d+$/', '', trim($validated['nama_subs']));

        // =========================================================================
        // CABANG KHUSUS: PARTIAL FULFILLMENT (SEBAGIAN DARI STOK & SISANYA BELI BARU)
        // =========================================================================
        if ($keputusan === 'split_existing_dan_baru') {
            $existingSubsIds       = array_values($request->input('existing_subs_ids', []));
            $existingAssignedUsers = array_values($request->input('existing_assigned_users', []));

            if (empty($existingSubsIds)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Pilih minimal 1 akun dari Master Subscription yang tersedia untuk mode Partial Fulfillment.']);
            }

            // 1. Proses Akun yang Sudah Tersedia -> Langsung Aktif & Masuk Tracking Penggunaan
            $fulfilledSubNames  = [];
            $fulfilledUserNames = [];

            foreach ($existingSubsIds as $idx => $exSubId) {
                $exSub = Subscription::find($exSubId);
                if (!$exSub) {
                    continue;
                }

                if ($exSub->status !== 'active') {
                    $exSub->update(['status' => 'active', 'is_active' => true]);
                }

                $kodePenerima = $existingAssignedUsers[$idx] ?? ($existingAssignedUsers[0] ?? $data->kode_karyawan);
                $penerimaKar  = karyawan::where('kode_karyawan', $kodePenerima)->first();

                $this->createDirectSubsAssignment(
                    $exSub,
                    $kodePenerima,
                    $itsmKaryawan,
                    "Pemenuhan Parsial (Stok Tersedia) dari Permintaan Subscription oleh {$data->karyawan->nama_lengkap}"
                );

                $fulfilledSubNames[]  = $exSub->nama_subs;
                $fulfilledUserNames[] = $penerimaKar ? ($penerimaKar->nama_lengkap . ' (' . $penerimaKar->jabatan . ')') : $kodePenerima;
            }

            // 2. Proses Sisa Akun yang Kurang -> Masuk ke Pengajuan Barang Finance
            $requestedQty     = (int) ($oldSnapshot['requested_qty'] ?? ($qty + count($existingSubsIds)));
            $fulfilledCount   = count($existingSubsIds);
            $qtyKurang        = (int) ($request->input('qty_pengadaan') ?: max(1, $requestedQty - $fulfilledCount));
            $totalHargaRupiah = $hargaSatuanRupiah * $qtyKurang;

            $splitSubsIds    = $oldSnapshot['split_subs_ids'] ?? [];
            $newSplitIds     = [];
            $createdSubsList = [];

            for ($i = 1; $i <= $qtyKurang; $i++) {
                $draftName = ($qtyKurang > 1) ? ($baseName . ' #' . $i) : $baseName;
                $draftSub  = null;

                // Gunakan record draft awal jika bukan termasuk stok existing yang baru saja di-assign
                if ($i === 1 && $data->subs && !in_array($data->id_subs, $existingSubsIds) && $data->subs->status === 'pending') {
                    $draftSub = $data->subs;
                } elseif (!empty($splitSubsIds[$i - 1]) && !in_array($splitSubsIds[$i - 1], $existingSubsIds)) {
                    $draftSub = Subscription::find($splitSubsIds[$i - 1]);
                }

                if ($draftSub) {
                    $draftSub->update([
                        'nama_subs'    => $draftName,
                        'merk'         => $validated['merk'],
                        'tipe'         => $validated['tipe'],
                        'desc'         => $validated['desc'] ?? ("Pengadaan Parsial #{$i} untuk Permintaan #{$data->id}"),
                        'subs_url'     => $validated['subs_url'],
                        'access_code'  => $validated['access_code'],
                        'mata_uang'    => $mataUang,
                        'harga'        => $hargaSatuanAsli,
                        'kurs'         => $kurs,
                        'harga_rupiah' => $hargaSatuanRupiah,
                        'start_date'   => $validated['start_date'],
                        'end_date'     => $validated['end_date'],
                        'status'       => 'pending',
                        'is_active'    => false,
                    ]);
                } else {
                    $draftSub = Subscription::create([
                        'kode_karyawan' => $itsmKaryawan->kode_karyawan,
                        'nama_subs'     => $draftName,
                        'merk'          => $validated['merk'],
                        'tipe'          => $validated['tipe'],
                        'desc'          => $validated['desc'] ?? ("Pengadaan Parsial #{$i} untuk Permintaan #{$data->id}"),
                        'subs_url'      => $validated['subs_url'],
                        'access_code'   => $validated['access_code'],
                        'mata_uang'     => $mataUang,
                        'harga'         => $hargaSatuanAsli,
                        'kurs'          => $kurs,
                        'harga_rupiah'  => $hargaSatuanRupiah,
                        'start_date'    => $validated['start_date'],
                        'end_date'      => $validated['end_date'],
                        'status'        => 'pending',
                        'is_active'     => false,
                    ]);
                }

                $createdSubsList[] = $draftSub;
                $newSplitIds[]     = $draftSub->id;
            }

            $primaryDraft = $createdSubsList[0];

            $snapshotData = array_merge($oldSnapshot, $primaryDraft->fresh()->toArray());
            $snapshotData['nama_subs']               = $baseName;
            $snapshotData['handled_by']              = $itsmKaryawan->nama_lengkap;
            $snapshotData['handled_by_jabatan']      = $itsmKaryawan->jabatan;
            $snapshotData['requested_qty']           = $requestedQty;
            $snapshotData['fulfilled_existing_qty']  = $fulfilledCount;
            $snapshotData['fulfilled_existing_subs'] = $fulfilledSubNames;
            $snapshotData['fulfilled_existing_users']= $fulfilledUserNames;
            $snapshotData['qty']                     = $qtyKurang;
            $snapshotData['harga']                   = $hargaSatuanAsli;
            $snapshotData['harga_satuan_rupiah']     = $hargaSatuanRupiah;
            $snapshotData['harga_rupiah']            = $totalHargaRupiah;
            $snapshotData['partial_fulfillment']     = true;
            $snapshotData['split_subs_ids']          = $newSplitIds;

            $existingPengajuanBarang = PengajuanBarang::where('tipe', 'Subscription')
                ->where('id_kegiatan', $data->id)
                ->first();

            $statusFinance   = "Telah disetujui oleh Koordinator ITSM dan sedang diproses oleh Finance";
            $namaBarangLabel = $baseName . ($primaryDraft->merk ? " ({$primaryDraft->merk})" : '');
            $partialTrackMsg = "Pemenuhan Parsial: {$fulfilledCount} akun diberikan dari stok, dan {$qtyKurang} akun diajukan ke Finance oleh {$itsmKaryawan->nama_lengkap}";

            if (!$existingPengajuanBarang) {
                $pengajuanBarang = PengajuanBarang::create([
                    'id_karyawan' => $itsmKaryawan->id,
                    'id_kegiatan' => $data->id,
                    'tipe'        => 'Subscription',
                ]);

                detailPengajuanBarang::create([
                    'id_pengajuan_barang' => $pengajuanBarang->id,
                    'nama_barang'         => $namaBarangLabel,
                    'qty'                 => $qtyKurang,
                    'harga'               => $hargaSatuanRupiah,
                    'keterangan'          => "Pemenuhan Parsial Subscription ({$qtyKurang} Akun Baru dari total {$requestedQty} Akun) oleh {$itsmKaryawan->nama_lengkap} ({$itsmKaryawan->jabatan})",
                ]);

                $trackingBarang = tracking_pengajuan_barang::create([
                    'id_pengajuan_barang' => $pengajuanBarang->id,
                    'tracking'            => $statusFinance,
                    'tanggal'             => now(),
                ]);

                $pengajuanBarang->update([
                    'id_tracking'            => $trackingBarang->id,
                    'tanggal_terima_finance' => now(),
                ]);
            } else {
                detailPengajuanBarang::where('id_pengajuan_barang', $existingPengajuanBarang->id)->update([
                    'nama_barang' => $namaBarangLabel,
                    'qty'         => $qtyKurang,
                    'harga'       => $hargaSatuanRupiah,
                ]);
            }

            $trackSubs = TrackingPengajuanSubs::create([
                'id_pengajuan_subs' => $data->id,
                'tracking'          => $partialTrackMsg,
                'tanggal'           => now(),
            ]);

            $financeKar  = karyawan::where('jabatan', 'Finance & Accounting')->first();
            $targetCodes = [$data->kode_karyawan, $itsmKaryawan->kode_karyawan];
            if ($financeKar) {
                $targetCodes[] = $financeKar->kode_karyawan;
            }

            $notifUsers = User::with('karyawan')
                ->whereHas('karyawan', fn($q) => $q->whereIn('kode_karyawan', array_unique($targetCodes)))
                ->get();
            foreach ($notifUsers as $u) {
                $notifPath = ($u->karyawan?->jabatan === 'Finance & Accounting')
                    ? '/pengajuanbarang'
                    : '/pengajuansubs';

                NotificationFacade::send(
                    $u,
                    new ApprovalLabSubsNotification(
                        ['tanggal' => now(), 'status' => $partialTrackMsg],
                        $notifPath,
                        $itsmKaryawan->nama_lengkap,
                        'Menyetujui Pengajuan Lab/Subscription',
                        $u->id
                    )
                );
            }

            $data->update([
                'id_subs'         => $primaryDraft->id,
                'jenis_transaksi' => 'baru',
                'subs_snapshot'   => $snapshotData,
                'id_tracking'     => $trackSubs->id,
            ]);

            return redirect()->route('pengajuansubs.index')
                ->with('success', "Pemenuhan Parsial berhasil: {$fulfilledCount} akun langsung diberikan dari stok, dan {$qtyKurang} akun baru berhasil diajukan ke Finance!");
        }

        // =========================================================================
        // CABANG REGULER (FULL EXISTING ATAU FULL PENGAJUAN BARANG)
        // =========================================================================
        $totalHargaRupiah = $hargaSatuanRupiah * $qty;
        $targetSubsId     = $validated['id_subs_selected'] ?: $data->id_subs;
        $subs             = Subscription::findOrFail($targetSubsId);

        $shouldSplit     = $request->boolean('split_master_subs') && $qty > 1;
        $createdSubsList = [];
        $splitSubsIds    = $oldSnapshot['split_subs_ids'] ?? [];

        if ($shouldSplit) {
            // Akun #1 adalah record utama $subs
            $subs->update([
                'nama_subs'    => $baseName . ' #1',
                'merk'         => $validated['merk'],
                'tipe'         => $validated['tipe'],
                'desc'         => $validated['desc'],
                'subs_url'     => $validated['subs_url'],
                'access_code'  => $validated['access_code'],
                'mata_uang'    => $mataUang,
                'harga'        => $hargaSatuanAsli,
                'kurs'         => $kurs,
                'harga_rupiah' => $hargaSatuanRupiah,
                'start_date'   => $validated['start_date'],
                'end_date'     => $validated['end_date'],
                'status'       => $validated['status'],
                'is_active'    => ($validated['status'] === 'active'),
            ]);
            $createdSubsList[] = $subs;

            // Buat atau perbarui akun #2 s/d #$qty agar tidak duplikat jika di-submit ulang
            $newSplitIds = [$subs->id];
            for ($i = 2; $i <= $qty; $i++) {
                $existingSplitId = $splitSubsIds[$i - 1] ?? null;
                $splitSub        = $existingSplitId ? Subscription::find($existingSplitId) : null;

                if ($splitSub) {
                    $splitSub->update([
                        'nama_subs'    => $baseName . ' #' . $i,
                        'merk'         => $validated['merk'],
                        'tipe'         => $validated['tipe'],
                        'mata_uang'    => $mataUang,
                        'harga'        => $hargaSatuanAsli,
                        'kurs'         => $kurs,
                        'harga_rupiah' => $hargaSatuanRupiah,
                        'start_date'   => $validated['start_date'],
                        'end_date'     => $validated['end_date'],
                        'status'       => $validated['status'],
                        'is_active'    => ($validated['status'] === 'active'),
                    ]);
                } else {
                    $splitSub = Subscription::create([
                        'kode_karyawan' => $itsmKaryawan->kode_karyawan,
                        'nama_subs'     => $baseName . ' #' . $i,
                        'merk'          => $validated['merk'],
                        'tipe'          => $validated['tipe'],
                        'desc'          => "[Akun #{$i}] " . ($validated['desc'] ?? ''),
                        'subs_url'      => $validated['subs_url'],
                        'access_code'   => $validated['access_code'],
                        'mata_uang'     => $mataUang,
                        'harga'         => $hargaSatuanAsli,
                        'kurs'          => $kurs,
                        'harga_rupiah'  => $hargaSatuanRupiah,
                        'start_date'    => $validated['start_date'],
                        'end_date'      => $validated['end_date'],
                        'status'        => $validated['status'],
                        'is_active'     => ($validated['status'] === 'active'),
                    ]);
                }
                $createdSubsList[] = $splitSub;
                $newSplitIds[]     = $splitSub->id;
            }
            $splitSubsIds = $newSplitIds;
        } else {
            $subs->update([
                'nama_subs'    => $validated['nama_subs'],
                'merk'         => $validated['merk'],
                'tipe'         => $validated['tipe'],
                'desc'         => $validated['desc'],
                'subs_url'     => $validated['subs_url'],
                'access_code'  => $validated['access_code'],
                'mata_uang'    => $mataUang,
                'harga'        => $hargaSatuanAsli,
                'kurs'         => $kurs,
                'harga_rupiah' => $hargaSatuanRupiah,
                'start_date'   => $validated['start_date'],
                'end_date'     => $validated['end_date'],
                'status'       => $validated['status'],
                'is_active'    => ($validated['status'] === 'active'),
            ]);
            $createdSubsList[] = $subs;
        }

        $snapshotData = array_merge($oldSnapshot, $subs->fresh()->toArray());
        $snapshotData['nama_subs']           = $shouldSplit ? $baseName : $subs->nama_subs;
        $snapshotData['handled_by']          = $itsmKaryawan->nama_lengkap;
        $snapshotData['handled_by_jabatan']  = $itsmKaryawan->jabatan;
        $snapshotData['requested_qty']       = $oldSnapshot['requested_qty'] ?? $qty;
        $snapshotData['qty']                 = $qty;
        $snapshotData['harga']               = $hargaSatuanAsli;
        $snapshotData['harga_satuan_rupiah'] = $hargaSatuanRupiah;
        $snapshotData['harga_rupiah']        = $totalHargaRupiah;
        $snapshotData['split_subs_ids']      = $splitSubsIds;

        // CABANG 1: SUBSCRIPTION AKTIF -> BERIKAN AKSES
        if ($keputusan === 'berikan_akses') {
            foreach ($createdSubsList as $sItem) {
                $sItem->update(['status' => 'active', 'is_active' => true]);
            }
            $snapshotData['status'] = 'active';

            $statusText = "Telah disetujui oleh Koordinator ITSM dan lihat akses nya di Detail (Ditangani oleh {$itsmKaryawan->nama_lengkap})";

            TrackingPengajuanSubs::create([
                'id_pengajuan_subs' => $data->id,
                'tracking'          => $statusText,
                'tanggal'           => now(),
            ]);

            $finalTrack = TrackingPengajuanSubs::create([
                'id_pengajuan_subs' => $data->id,
                'tracking'          => 'Selesai - Akses Subscription Siap Digunakan',
                'tanggal'           => now()->addSeconds(1),
            ]);

            $data->update([
                'id_subs'         => $subs->id,
                'jenis_transaksi' => 'existing',
                'subs_snapshot'   => $snapshotData,
                'id_tracking'     => $finalTrack->id,
            ]);

            $pemintaUsers = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $data->kode_karyawan))->get();
            foreach ($pemintaUsers as $u) {
                NotificationFacade::send(
                    $u,
                    new ApprovalLabSubsNotification(
                        ['tanggal' => now(), 'status' => $statusText],
                        '/pengajuansubs',
                        $data->karyawan->nama_lengkap,
                        'Menyetujui Pengajuan Lab/Subscription',
                        $u->id
                    )
                );
            }

            return redirect()->route('pengajuansubs.index')
                ->with('success', 'Akses Subscription berhasil diberikan kepada ' . ($data->karyawan->nama_lengkap ?? 'peminta') . '!');
        }

        // CABANG 2: SUBSCRIPTION BARU / EXPIRED -> PENGAJUAN BARANG ATAS NAMA TIM ITSM
        $jenisTransaksi = ($keputusan === 'pengajuan_barang_perpanjangan') ? 'pembaharuan' : 'baru';

        $existingPengajuanBarang = PengajuanBarang::where('tipe', 'Subscription')
            ->where('id_kegiatan', $data->id)
            ->first();

        $statusFinance   = "Telah disetujui oleh Koordinator ITSM dan sedang diproses oleh Finance";
        $namaBarangLabel = ($shouldSplit ? $baseName : $subs->nama_subs) . ($subs->merk ? " ({$subs->merk})" : '');

        if (!$existingPengajuanBarang) {
            $pengajuanBarang = PengajuanBarang::create([
                'id_karyawan' => $itsmKaryawan->id,
                'id_kegiatan' => $data->id,
                'tipe'        => 'Subscription',
            ]);

            detailPengajuanBarang::create([
                'id_pengajuan_barang' => $pengajuanBarang->id,
                'nama_barang'         => $namaBarangLabel,
                'qty'                 => $qty,
                'harga'               => $hargaSatuanRupiah,
                'keterangan'          => 'Pengadaan ' . ucfirst($jenisTransaksi) . " {$qty} Akun Subscription oleh " . $itsmKaryawan->nama_lengkap . ' (' . $itsmKaryawan->jabatan . ')',
            ]);

            $trackingBarang = tracking_pengajuan_barang::create([
                'id_pengajuan_barang' => $pengajuanBarang->id,
                'tracking'            => $statusFinance,
                'tanggal'             => now(),
            ]);

            $pengajuanBarang->update([
                'id_tracking'            => $trackingBarang->id,
                'tanggal_terima_finance' => now(),
            ]);

            $trackSubs = TrackingPengajuanSubs::create([
                'id_pengajuan_subs' => $data->id,
                'tracking'          => "Dialihkan ke Pengajuan Barang ({$qty} Akun) oleh {$itsmKaryawan->nama_lengkap} ({$itsmKaryawan->jabatan})",
                'tanggal'           => now(),
            ]);

            $financeKar  = karyawan::where('jabatan', 'Finance & Accounting')->first();
            $targetCodes = [$data->kode_karyawan, $itsmKaryawan->kode_karyawan];
            if ($financeKar) {
                $targetCodes[] = $financeKar->kode_karyawan;
            }

            $notifUsers = User::with('karyawan')
                ->whereHas('karyawan', fn($q) => $q->whereIn('kode_karyawan', array_unique($targetCodes)))
                ->get();
            foreach ($notifUsers as $u) {
                $notifPath = ($u->karyawan?->jabatan === 'Finance & Accounting')
                    ? '/pengajuanbarang'
                    : '/pengajuansubs';

                NotificationFacade::send(
                    $u,
                    new ApprovalLabSubsNotification(
                        ['tanggal' => now(), 'status' => $statusFinance],
                        $notifPath,
                        $itsmKaryawan->nama_lengkap,
                        'Menyetujui Pengajuan Lab/Subscription',
                        $u->id
                    )
                );
            }
        } else {
            detailPengajuanBarang::where('id_pengajuan_barang', $existingPengajuanBarang->id)->update([
                'nama_barang' => $namaBarangLabel,
                'qty'         => $qty,
                'harga'       => $hargaSatuanRupiah,
            ]);

            $trackSubs = TrackingPengajuanSubs::create([
                'id_pengajuan_subs' => $data->id,
                'tracking'          => "Data Teknis Subscription ({$qty} Akun) diperbarui oleh {$itsmKaryawan->nama_lengkap}",
                'tanggal'           => now(),
            ]);
        }

        $data->update([
            'id_subs'         => $subs->id,
            'jenis_transaksi' => $jenisTransaksi,
            'subs_snapshot'   => $snapshotData,
            'id_tracking'     => $trackSubs->id,
        ]);

        return redirect()->route('pengajuansubs.index')
            ->with('success', "Pengajuan Barang ({$qty} Akun Subscription) atas nama {$itsmKaryawan->nama_lengkap} berhasil diteruskan ke Finance!");
    }

    public function show($id)
    {
        $data = PengajuanSubs::with(['karyawan', 'subs', 'tracking', 'rkm.perusahaan', 'rkm.materi'])
            ->findOrFail($id);

        $pengajuanBarang = PengajuanBarang::where('tipe', 'Subscription')
            ->where('id_kegiatan', $data->id)
            ->with('karyawan')
            ->first();

        if ($pengajuanBarang) {
            $trackingBarang = tracking_pengajuan_barang::where('id_pengajuan_barang', $pengajuanBarang->id)->get();
            $mergedTracking = $data->tracking->concat($trackingBarang)->sortBy('tanggal')->values();
            $data->setRelation('tracking', $mergedTracking);
        }

        return view('pengajuansubs.show', compact('data', 'pengajuanBarang'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'approval'       => 'required|string',
            'alasan'         => 'nullable|string|max:500',
            'finance_status' => 'nullable',
        ]);

        $data    = PengajuanSubs::with('karyawan')->findOrFail($id);
        $jabatan = auth()->user()->karyawan->jabatan;

        if ($jabatan === 'Finance & Accounting' && !in_array($request->approval, ['1', '2'])) {
            $status = $request->approval;

            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status pencairan wajib dipilih.'
                ], 400);
            }

            $e = TrackingPengajuanSubs::create([
                'id_pengajuan_subs' => $id,
                'tracking'          => $status,
                'tanggal'           => now()
            ]);

            $data->update(['id_tracking' => $e->id]);

            if ($status === 'Pencairan Sudah Selesai') {
                $final = TrackingPengajuanSubs::create([
                    'id_pengajuan_subs' => $id,
                    'tracking'          => 'Selesai',
                    'tanggal'           => now()->addSeconds(1)
                ]);

                $data->update(['id_tracking' => $final->id]);
            }

            $userObjs = User::whereHas('karyawan', function ($q) use ($data) {
                $q->where('kode_karyawan', $data->karyawan->kode_karyawan);
            })->get();

            foreach ($userObjs as $user) {
                NotificationFacade::send(
                    $user,
                    new ApprovalLabSubsNotification(
                        ['tanggal' => now(), 'status' => $status],
                        '/pengajuansubs',
                        $data->karyawan->nama_lengkap,
                        'Update Status Pencairan oleh Finance',
                        $user->id
                    )
                );
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Status pencairan berhasil diperbarui!',
                'redirect' => route('pengajuansubs.index')
            ]);
        }

        if ($request->approval == '1') {
            if (!in_array($jabatan, ['Education Manager', 'SPV Sales', 'GM'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Untuk Koordinator ITSM / Technical Support, silakan gunakan tombol Proses Subscription.'
                ], 403);
            }

            $status = "Telah disetujui oleh {$jabatan} dan sedang ditinjau oleh Koordinator ITSM";

            $e = TrackingPengajuanSubs::create([
                'id_pengajuan_subs' => $id,
                'tracking'          => $status,
                'tanggal'           => now()
            ]);

            $data->update(['id_tracking' => $e->id]);

            $itsmStaff  = karyawan::whereIn('jabatan', ['Koordinator ITSM', 'Technical Support'])->pluck('kode_karyawan')->toArray();
            $usersCodes = array_unique(array_merge([$data->karyawan->kode_karyawan], $itsmStaff));

            $userObjs = User::whereHas('karyawan', fn($q) => $q->whereIn('kode_karyawan', $usersCodes))->get();

            foreach ($userObjs as $user) {
                NotificationFacade::send(
                    $user,
                    new ApprovalLabSubsNotification(
                        ['tanggal' => now(), 'status' => $status],
                        '/pengajuansubs',
                        $data->karyawan->nama_lengkap,
                        'Menyetujui Pengajuan Lab/Subscription',
                        $user->id
                    )
                );
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Permintaan Subscription disetujui dan diteruskan ke Management Subscription (ITSM)!',
                'redirect' => route('pengajuansubs.index')
            ]);
        }

        if ($request->approval == '2') {
            $alasan = $request->alasan ?? 'Tidak disebutkan';
            $status = "Pengajuan ditolak oleh {$jabatan} karena {$alasan}";

            $e = TrackingPengajuanSubs::create([
                'id_pengajuan_subs' => $id,
                'tracking'          => $status,
                'tanggal'           => now()
            ]);

            $data->update(['id_tracking' => $e->id]);

            $userObjs = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $data->karyawan->kode_karyawan))->get();

            foreach ($userObjs as $user) {
                NotificationFacade::send(
                    $user,
                    new ApprovalLabSubsNotification(
                        ['tanggal' => now(), 'status' => $status],
                        '/pengajuansubs',
                        $data->karyawan->nama_lengkap,
                        'Menolak Pengajuan Lab/Subscription',
                        $user->id
                    )
                );
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Pengajuan berhasil ditolak!',
                'redirect' => route('pengajuansubs.index')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada aksi yang diproses!'
        ], 400);
    }

    public function uploadInvoice(Request $request, $id)
    {
        $request->validate([
            'invoice' => 'required|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = PengajuanSubs::findOrFail($id);

        if ($data->invoice && Storage::exists('public/pengajuansubs/' . $data->invoice)) {
            Storage::delete('public/pengajuansubs/' . $data->invoice);
        }

        $filename = 'invoice_subs_' . $id . '_' . time() . '.' . $request->file('invoice')->getClientOriginalExtension();
        $request->file('invoice')->storeAs('public/pengajuansubs', $filename);

        $data->update(['invoice' => $filename]);

        $pengajuanBarang = PengajuanBarang::where('tipe', 'Subscription')->where('id_kegiatan', $data->id)->first();
        if ($pengajuanBarang) {
            $pengajuanBarang->update(['invoice' => 'pengajuansubs/' . $filename]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil diunggah!',
            'file'    => asset('storage/pengajuansubs/' . $filename),
        ]);
    }

    public function exportPDF($id)
    {
        $data = PengajuanSubs::with(['subs', 'karyawan', 'tracking'])->findOrFail($id);

        $subsSnapshot = null;
        if (!empty($data->subs_snapshot)) {
            $subsSnapshot = is_string($data->subs_snapshot) ? json_decode($data->subs_snapshot) : (object) $data->subs_snapshot;
        } elseif ($data->subs) {
            $subsSnapshot = (object) $data->subs->toArray();
        }

        if ($data->karyawan->divisi == 'Education') {
            $finance = karyawan::where('jabatan', 'Education Manager')->latest()->first();
        } elseif ($data->karyawan->divisi == 'Sales & Marketing') {
            $finance = karyawan::where('jabatan', 'SPV Sales')->latest()->first();
        } elseif ($data->karyawan->divisi == 'Office') {
            $finance = karyawan::where('jabatan', 'GM')->latest()->first();
        } elseif ($data->karyawan->divisi == 'IT Service Management') {
            $finance = karyawan::where('jabatan', 'Koordinator ITSM')->latest()->first();
        } else {
            $finance = null;
        }

        $gm = karyawan::where('jabatan', 'GM')->latest()->first();

        return view('exports.pengajuan_subs-pdf', compact('data', 'finance', 'gm', 'subsSnapshot'));
    }

    public function renewSubs($id)
    {
        $user         = auth()->user();
        $itsmKaryawan = $user->karyawan;

        $subs = Subscription::findOrFail($id);

        $pengajuan = PengajuanSubs::create([
            'kode_karyawan'   => $itsmKaryawan->kode_karyawan,
            'id_subs'         => $subs->id,
            'id_rkm'          => null,
            'jenis_transaksi' => 'pembaharuan',
            'subs_snapshot'   => array_merge($subs->toArray(), [
                'handled_by'         => $itsmKaryawan->nama_lengkap,
                'handled_by_jabatan' => $itsmKaryawan->jabatan,
                'requested_qty'      => 1,
                'qty'                => 1,
            ]),
        ]);

        $trackingText = 'Pengajuan Pembaharuan Subscription Diajukan oleh ' . $itsmKaryawan->nama_lengkap . ' - Silakan lengkapi harga & teruskan ke Finance';

        $trackingModel = TrackingPengajuanSubs::create([
            'id_pengajuan_subs' => $pengajuan->id,
            'tracking'          => $trackingText,
            'tanggal'           => now(),
        ]);

        $pengajuan->update(['id_tracking' => $trackingModel->id]);

        return response()->json([
            'success' => true,
            'message' => 'Draft perpanjangan subscription ' . $subs->nama_subs . ' berhasil dibuat! Silakan klik Proses Subscription pada tabel untuk meneruskan ke Finance.'
        ]);
    }

    public function destroy($id)
    {
        $pengajuan = PengajuanSubs::findOrFail($id);

        $pengajuan->tracking()->delete();

        if ($pengajuan->jenis_transaksi === 'baru' && $pengajuan->id_subs) {
            $subs = Subscription::find($pengajuan->id_subs);
            if ($subs) {
                $subs->materis()->detach();
                $subs->delete();
            }
        }

        if ($pengajuan->invoice && Storage::exists('public/pengajuansubs/' . $pengajuan->invoice)) {
            Storage::delete('public/pengajuansubs/' . $pengajuan->invoice);
        }

        $pengajuan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permintaan subscription berhasil dihapus.'
        ]);
    }
}
