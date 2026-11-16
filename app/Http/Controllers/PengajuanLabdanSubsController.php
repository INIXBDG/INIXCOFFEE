<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\karyawan;
use App\Models\PengajuanBarang;
use App\Models\detailPengajuanBarang;
use App\Models\User;
use App\Models\RKM;
use App\Models\Materi;
use App\Models\Lab;
use App\Models\PengajuanLabSubs;
use App\Models\TrackingPengajuanLabSubs;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\PengajuanLabdanSubsNotification;
use App\Notifications\ApprovalLabSubsNotification;
use App\Models\tracking_pengajuan_barang;
use Illuminate\Support\Facades\Storage;

class PengajuanLabdanSubsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user || !$user->karyawan) {
            return view('auth.login');
        }

        $tracking = 'buka';
        $materis  = Materi::orderBy('nama_materi', 'asc')->get();

        // Data Instruktur/Eduman & RKM untuk fitur Assign Pengguna Lab oleh Tim ITSM
        $instrukturs = karyawan::whereIn('jabatan', ['Instruktur', 'Education Manager', 'Technical Support', 'Koordinator ITSM'])
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $rkms = RKM::with(['materi', 'perusahaan'])
            ->whereDate('tanggal_akhir', '>=', now()->subMonths(1))
            ->orderBy('tanggal_awal', 'desc')
            ->get();

        return view('pengajuanlabs.index', compact('tracking', 'materis', 'instrukturs', 'rkms'));
    }

    public function getPengajuanLabSubs($month, $year)
    {
        $user     = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $jabatan  = $karyawan->jabatan;
        $divisi   = $karyawan->divisi;

        $relations = ['karyawan', 'tracking', 'lab', 'rkm.perusahaan', 'rkm.materi'];

        if (in_array($jabatan, ['Finance & Accounting', 'Koordinator ITSM', 'GM', 'Technical Support', 'Education Manager'])) {
            $Pengajuan = PengajuanLabSubs::with($relations)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->latest()
                ->get();
        } elseif (in_array($jabatan, ['Office Manager', 'SPV Sales'])) {
            $Pengajuan = PengajuanLabSubs::with($relations)
                ->whereHas('karyawan', function ($q) use ($divisi) {
                    $q->where('divisi', $divisi);
                })
                ->latest()
                ->get();
        } else {
            $Pengajuan = PengajuanLabSubs::with($relations)
                ->where('kode_karyawan', $karyawan->kode_karyawan)
                ->latest()
                ->get();
        }

        $Pengajuan->transform(function ($item) {
            $pengajuanBarang = PengajuanBarang::where('tipe', 'Lab')
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
            'message' => 'List Pengajuan Lab',
            'data'    => $Pengajuan,
        ]);
    }

    public function getMasterLabs()
    {
        $labs = Lab::with(['materis', 'pengajuans.karyawan', 'pengajuans.rkm.perusahaan', 'pengajuans.tracking'])
            ->orderBy('created_at', 'desc')
            ->get();

        $labs->transform(function ($lab) {
            // PERBAIKAN: Tambahkan "use ($lab)" pada function ($p) di bawah ini
            $activeUsers = $lab->pengajuans->filter(function ($p) use ($lab) {
                $tracks = $p->tracking->pluck('tracking')->implode(' ');
                $hasAccess = str_contains(strtolower($tracks), 'lihat akses nya di detail') || str_contains(strtolower($tracks), 'akses lab telah diberikan');

                if (!$hasAccess) {
                    return false;
                }

                // Jika terikat RKM, cek apakah tanggal akhir RKM masih berlaku
                if ($p->rkm && $p->rkm->tanggal_akhir) {
                    return now()->startOfDay()->lte($p->rkm->tanggal_akhir);
                }

                // Jika non-RKM, cek apakah masa aktif lab masih berlaku
                if ($lab->end_date) {
                    return now()->startOfDay()->lte($lab->end_date);
                }

                return $lab->status === 'active';
            })->map(function ($p) {
                return [
                    'pengajuan_id' => $p->id,
                    'nama'         => $p->karyawan->nama_lengkap ?? '-',
                    'jabatan'      => $p->karyawan->jabatan ?? '-',
                    'perusahaan'   => $p->rkm?->perusahaan?->nama_perusahaan ?? 'Non-RKM',
                    'periode'      => ($p->rkm && $p->rkm->tanggal_awal)
                        ? \Carbon\Carbon::parse($p->rkm->tanggal_awal)->format('d/m/y') . ' - ' . \Carbon\Carbon::parse($p->rkm->tanggal_akhir)->format('d/m/y')
                        : 'Mengikuti Masa Aktif Lab'
                ];
            })->values();

            $lab->active_users = $activeUsers;
            return $lab;
        });

        return response()->json([
            'success' => true,
            'data'    => $labs
        ]);
    }

    /**
     * FITUR BARU 1: Tambah Master Lab yang sudah berjalan (Oleh Koor ITSM / Technical Support)
     */
    public function storeMasterLab(Request $request)
    {
        $user         = auth()->user();
        $itsmKaryawan = $user->karyawan;

        if (!in_array($itsmKaryawan->jabatan, ['Technical Support', 'Koordinator ITSM'])) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki akses.'], 403);
        }

        $request->validate([
            'nama_labs'             => 'required|string|max:255',
            'merk'                  => 'nullable|string|max:255',
            'tipe'                  => 'required|in:subscription,one-time',
            'status'                => 'required|in:active,pending,expired',
            'duration_minutes'      => 'nullable|numeric',
            'desc'                  => 'nullable|string',
            'lab_url'               => 'nullable|string|max:255',
            'access_code'           => 'nullable|string|max:255',
            'start_date'            => 'nullable|date',
            'end_date'              => 'nullable|date',
            'mata_uang'             => 'nullable|string|max:50',
            'harga'                 => 'nullable|numeric',
            'kurs'                  => 'nullable|numeric',
            'harga_rupiah'          => 'nullable|numeric',
            'materi_ids'            => 'nullable|array',
            'materi_ids.*'          => 'exists:materis,id',
            'assigned_kode_karyawan'=> 'nullable|array',
            'assigned_id_rkm'       => 'nullable|exists:r_k_m_s,id',
        ]);

        $lab = Lab::create([
            'kode_karyawan'    => $itsmKaryawan->kode_karyawan,
            'nama_labs'        => $request->nama_labs,
            'merk'             => $request->merk,
            'tipe'             => $request->tipe,
            'duration_minutes' => $request->duration_minutes,
            'status'           => $request->status,
            'is_active'        => ($request->status === 'active'),
            'desc'             => $request->desc,
            'lab_url'          => $request->lab_url,
            'access_code'      => $request->access_code,
            'start_date'       => $request->start_date,
            'end_date'         => $request->end_date,
            'mata_uang'        => $request->mata_uang ?? 'Rupiah',
            'harga'            => $request->harga ?? 0,
            'kurs'             => $request->kurs ?? 1,
            'harga_rupiah'     => $request->harga_rupiah ?? 0,
        ]);

        if ($request->filled('materi_ids')) {
            $lab->materis()->sync($request->materi_ids);
        }

        // Jika saat membuat lab yang sudah berjalan sekaligus meng-assign instruktur yang sedang memakai
        if ($request->filled('assigned_kode_karyawan') && is_array($request->assigned_kode_karyawan)) {
            foreach ($request->assigned_kode_karyawan as $kodeKaryawan) {
                $this->createDirectAccessAssignment($lab, $kodeKaryawan, $request->assigned_id_rkm, $itsmKaryawan);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Lab yang sedang berjalan berhasil ditambahkan ke Master Lab!',
            'data'    => $lab
        ]);
    }

    /**
     * FITUR BARU 2: Assign Instruktur yang sedang menggunakan Lab dari Management Master Lab
     */
    public function assignLabUser(Request $request, $id)
    {
        $user         = auth()->user();
        $itsmKaryawan = $user->karyawan;

        if (!in_array($itsmKaryawan->jabatan, ['Technical Support', 'Koordinator ITSM'])) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki akses.'], 403);
        }

        $request->validate([
            'kode_karyawan_list'   => 'required|array|min:1',
            'kode_karyawan_list.*' => 'required|exists:karyawans,kode_karyawan',
            'id_rkm'               => 'nullable|exists:r_k_m_s,id',
            'catatan_assign'       => 'nullable|string|max:500',
        ]);

        $lab = Lab::findOrFail($id);

        // Pastikan status lab aktif saat di-assign
        if ($lab->status !== 'active') {
            $lab->update(['status' => 'active', 'is_active' => true]);
        }

        foreach ($request->kode_karyawan_list as $kodeKaryawan) {
            $this->createDirectAccessAssignment($lab, $kodeKaryawan, $request->id_rkm, $itsmKaryawan, $request->catatan_assign);
        }

        return response()->json([
            'success' => true,
            'message' => 'Instruktur berhasil di-assign dan akses lab telah dicatat ke dalam Tracking Penggunaan!'
        ]);
    }

    /**
     * Helper internal untuk mencatat assignment instruktur langsung sebagai Permintaan Akses yang Disetujui
     */
    private function createDirectAccessAssignment(Lab $lab, string $kodeKaryawan, $rkmId, $itsmKaryawan, $catatan = null)
    {
        $instruktur = karyawan::where('kode_karyawan', $kodeKaryawan)->first();
        if (!$instruktur) {
            return;
        }

        $snapshot = $lab->toArray();
        $snapshot['handled_by']         = $itsmKaryawan->nama_lengkap;
        $snapshot['handled_by_jabatan'] = $itsmKaryawan->jabatan;
        $snapshot['requested_catatan']  = $catatan ?? 'Di-assign langsung oleh Tim ITSM (' . $itsmKaryawan->nama_lengkap . ')';

        $pengajuan = PengajuanLabSubs::create([
            'kode_karyawan'   => $instruktur->kode_karyawan,
            'id_labs'         => $lab->id,
            'id_rkm'          => $rkmId ?: null,
            'jenis_transaksi' => 'existing',
            'lab_snapshot'    => $snapshot,
        ]);

        $statusText = "Telah disetujui oleh Koordinator ITSM dan lihat akses nya di Detail (Di-assign oleh {$itsmKaryawan->nama_lengkap})";

        TrackingPengajuanLabSubs::create([
            'id_pengajuan_lab_subs' => $pengajuan->id,
            'tracking'              => $statusText,
            'tanggal'               => now(),
        ]);

        $finalTrack = TrackingPengajuanLabSubs::create([
            'id_pengajuan_lab_subs' => $pengajuan->id,
            'tracking'              => 'Selesai - Akses Lab Siap Digunakan',
            'tanggal'               => now()->addSeconds(1),
        ]);

        $pengajuan->update(['id_tracking' => $finalTrack->id]);

        // Kirim notifikasi ke Instruktur bahwa ia telah di-assign akses lab
        $targetUsers = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $instruktur->kode_karyawan))->get();
        foreach ($targetUsers as $u) {
            NotificationFacade::send(
                $u,
                new ApprovalLabSubsNotification(
                    ['tanggal' => now(), 'status' => $statusText],
                    '/pengajuanlabsdansubs',
                    $instruktur->nama_lengkap,
                    'Menyetujui Pengajuan Lab/Subscription',
                    $u->id
                )
            );
        }
    }

    public function create()
    {
        $user     = auth()->user();
        $karyawan = $user->karyawan;

        $rkms = RKM::with(['materi', 'perusahaan'])
            ->whereDate('tanggal_awal', '>=', now())
            ->orderBy('tanggal_awal', 'asc')
            ->get();

        // Ambil daftar seluruh Master Lab agar peminta bisa langsung memilih dari yang sudah ada
        $masterLabs = Lab::orderBy('nama_labs', 'asc')->get();

        return view('pengajuanlabs.create', compact('karyawan', 'rkms', 'masterLabs'));
    }

    public function getLabsByRkm($rkmId)
    {
        $rkm = RKM::with('materi')->find($rkmId);

        if (!$rkm || !$rkm->materi) {
            return response()->json(['materi_nama' => '', 'labs' => []]);
        }

        $labs = $rkm->materi->labs()->get();

        return response()->json([
            'materi_nama' => $rkm->materi->nama_materi,
            'labs'        => $labs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_rkm'          => 'nullable|exists:r_k_m_s,id',
            'id_existing_lab' => 'nullable|exists:labs,id',
            'nama_labs'       => 'required|string|max:255',
            'merk'            => 'required|string|max:255',
            'catatan'         => 'nullable|string|max:500',
        ], [
            'id_rkm.exists'      => 'Jadwal kelas (RKM) yang dipilih tidak valid.',
            'nama_labs.required' => 'Nama Lab / Software wajib diisi.',
            'merk.required'      => 'Vendor / Merk wajib diisi.',
        ]);

        $karyawan = karyawan::where('kode_karyawan', $request->kode_karyawan)->firstOrFail();

        // 1. Jika peminta memilih dari dropdown Lab yang sudah ada
        if ($request->filled('id_existing_lab')) {
            $labId = $request->id_existing_lab;
        } else {
            // 2. Jika ketik manual, cek dulu apakah nama & merk sudah ada di Master Lab
            $existingLab = Lab::whereRaw('LOWER(nama_labs) = ?', [strtolower(trim($request->nama_labs))])
                ->whereRaw('LOWER(merk) = ?', [strtolower(trim($request->merk))])
                ->first();

            if ($existingLab) {
                $labId = $existingLab->id;
            } else {
                // Buat draft baru di Master Lab untuk dieksekusi oleh Tim ITSM
                $newLab = Lab::create([
                    'kode_karyawan' => $karyawan->kode_karyawan,
                    'nama_labs'     => trim($request->nama_labs),
                    'merk'          => trim($request->merk),
                    'tipe'          => 'one-time',
                    'desc'          => $request->catatan ?? ('Permintaan Lab oleh ' . $karyawan->nama_lengkap),
                    'lab_url'       => null,
                    'status'        => 'pending',
                    'is_active'     => false,
                ]);
                $labId = $newLab->id;
            }
        }

        $isEduman = ($karyawan->jabatan === 'Education Manager');

        $trackingText = $isEduman
            ? 'Diajukan oleh Education Manager dan sedang ditinjau oleh Koordinator ITSM'
            : 'Diajukan dan Sedang Ditinjau oleh Education Manager';

        $pengajuan = PengajuanLabSubs::create([
            'kode_karyawan'   => $karyawan->kode_karyawan,
            'id_labs'         => $labId,
            'id_rkm'          => $request->id_rkm ?: null,
            'jenis_transaksi' => 'existing',
            'lab_snapshot'    => [
                'requested_nama_labs' => trim($request->nama_labs),
                'requested_merk'      => trim($request->merk),
                'requested_catatan'   => $request->catatan,
            ],
        ]);

        $trackingModel = TrackingPengajuanLabSubs::create([
            'id_pengajuan_lab_subs' => $pengajuan->id,
            'tracking'              => $trackingText,
            'tanggal'               => now(),
        ]);

        $pengajuan->update(['id_tracking' => $trackingModel->id]);

        $rkmPayload = null;
        if ($request->filled('id_rkm')) {
            $rkm = RKM::with(['materi', 'perusahaan'])->find($request->id_rkm);
            if ($rkm) {
                $rkmPayload = [
                    'nama_materi'     => $rkm->materi->nama_materi ?? '-',
                    'nama_perusahaan' => $rkm->perusahaan->nama_perusahaan ?? '-',
                    'tanggal_awal'    => $rkm->tanggal_awal,
                    'tanggal_akhir'   => $rkm->tanggal_akhir,
                ];
            }
        }

        $notifData = [
            'id_karyawan'       => $karyawan->id,
            'tanggal_pengajuan' => now(),
            'jenis_pengajuan'   => 'lab',
            'nama'              => $request->nama_labs . ' (' . $request->merk . ')',
            'deskripsi'         => $request->catatan ?? ($rkmPayload ? 'Permintaan Lab Kelas RKM' : 'Permintaan Lab (Non-RKM)'),
            'rkm'               => $rkmPayload,
        ];

        $targetJabatans = $isEduman
            ? ['Koordinator ITSM', 'Technical Support']
            : ['Education Manager', 'Koordinator ITSM'];

        $receivers = User::whereHas('karyawan', function ($q) use ($targetJabatans) {
            $q->whereIn('jabatan', $targetJabatans);
        })->get();

        $path = "/pengajuanlabsdansubs";
        $type = "Mengajukan Lab";

        foreach ($receivers as $receiver) {
            NotificationFacade::send(
                $receiver,
                new PengajuanLabdanSubsNotification($notifData, $path, $type, $receiver->id)
            );
        }

        $msg = $isEduman
            ? 'Permintaan lab berhasil dikirim dan langsung masuk ke antrean Tim ITSM.'
            : 'Permintaan lab berhasil dikirim dan menunggu persetujuan Education Manager.';

        return redirect()->route('pengajuanlabsdansubs.index')->with('success', $msg);
    }

    public function edit($id)
    {
        $user    = auth()->user();
        $jabatan = $user->karyawan->jabatan;

        if (!in_array($jabatan, ['Technical Support', 'Koordinator ITSM'])) {
            return redirect()->route('pengajuanlabsdansubs.index')
                ->with('error', 'Anda tidak memiliki akses ke halaman Management Lab.');
        }

        $data       = PengajuanLabSubs::with(['lab', 'karyawan', 'rkm.materi', 'rkm.perusahaan'])->findOrFail($id);
        $masterLabs = Lab::orderBy('nama_labs', 'asc')->get();

        return view('pengajuanlabs.edit', compact('data', 'masterLabs'));
    }

    public function updateLabSubs(Request $request, $id)
    {
        $user         = auth()->user();
        $itsmKaryawan = $user->karyawan;

        if (!in_array($itsmKaryawan->jabatan, ['Technical Support', 'Koordinator ITSM'])) {
            return redirect()->route('pengajuanlabsdansubs.index')
                ->with('error', 'Aksi ini hanya dapat dilakukan oleh Koordinator ITSM atau Technical Support.');
        }

        $data = PengajuanLabSubs::with(['lab', 'karyawan', 'rkm'])->findOrFail($id);

        $validated = $request->validate([
            'id_labs_selected'  => 'nullable|exists:labs,id',
            'keputusan_itsm'    => 'required|in:berikan_akses,pengajuan_barang_baru,pengajuan_barang_perpanjangan',
            'nama_labs'         => 'required|string|max:255',
            'merk'              => 'nullable|string|max:255',
            'tipe'              => 'required|in:one-time,subscription',
            'desc'              => 'nullable|string',
            'lab_url'           => 'nullable|url',
            'access_code'       => 'nullable|string|max:255',
            'duration_minutes'  => 'nullable|numeric',
            'mata_uang'         => 'nullable|string|max:50',
            'harga'             => 'nullable|numeric',
            'kurs'              => 'nullable|numeric',
            'harga_rupiah'      => 'nullable|string',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date',
            'status'            => 'required|string|in:active,pending,expired',
        ]);

        $hargaAsli = $validated['harga'] ?? 0;
        $mataUang  = $validated['mata_uang'] ?? 'Rupiah';
        $kurs      = ($mataUang === 'Rupiah') ? 1 : ($validated['kurs'] ?? 1);

        if (!empty($validated['harga_rupiah'])) {
            $hargaRupiah = (int) preg_replace('/[^\d]/', '', $validated['harga_rupiah']);
        } else {
            $hargaRupiah = $hargaAsli * $kurs;
        }
        if ($mataUang === 'Rupiah') {
            $hargaRupiah = $hargaAsli;
        }

        $targetLabId = $validated['id_labs_selected'] ?: $data->id_labs;
        $lab         = Lab::findOrFail($targetLabId);

        $lab->update([
            'nama_labs'        => $validated['nama_labs'],
            'merk'             => $validated['merk'],
            'tipe'             => $validated['tipe'],
            'desc'             => $validated['desc'],
            'lab_url'          => $validated['lab_url'],
            'access_code'      => $validated['access_code'],
            'duration_minutes' => $validated['duration_minutes'],
            'mata_uang'        => $mataUang,
            'harga'            => $hargaAsli,
            'kurs'             => $kurs,
            'harga_rupiah'     => $hargaRupiah,
            'start_date'       => $validated['start_date'],
            'end_date'         => $validated['end_date'],
            'status'           => $validated['status'],
            'is_active'        => ($validated['status'] === 'active'),
        ]);

        if ($data->rkm && $data->rkm->materi_key) {
            $lab->materis()->syncWithoutDetaching([$data->rkm->materi_key]);
        }

        $snapshotData = $lab->fresh()->toArray();
        $snapshotData['handled_by']         = $itsmKaryawan->nama_lengkap;
        $snapshotData['handled_by_jabatan'] = $itsmKaryawan->jabatan;

        $keputusan = $validated['keputusan_itsm'];

        // CABANG 1: LAB AKTIF -> BERIKAN AKSES
        if ($keputusan === 'berikan_akses') {
            $lab->update(['status' => 'active', 'is_active' => true]);
            $snapshotData['status'] = 'active';

            $statusText = "Telah disetujui oleh Koordinator ITSM dan lihat akses nya di Detail (Ditangani oleh {$itsmKaryawan->nama_lengkap})";

            TrackingPengajuanLabSubs::create([
                'id_pengajuan_lab_subs' => $data->id,
                'tracking'              => $statusText,
                'tanggal'               => now(),
            ]);

            $finalTrack = TrackingPengajuanLabSubs::create([
                'id_pengajuan_lab_subs' => $data->id,
                'tracking'              => 'Selesai - Akses Lab Siap Digunakan',
                'tanggal'               => now()->addSeconds(1),
            ]);

            $data->update([
                'id_labs'         => $lab->id,
                'jenis_transaksi' => 'existing',
                'lab_snapshot'    => $snapshotData,
                'id_tracking'     => $finalTrack->id,
            ]);

            $pemintaUsers = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $data->kode_karyawan))->get();
            foreach ($pemintaUsers as $u) {
                NotificationFacade::send(
                    $u,
                    new ApprovalLabSubsNotification(
                        ['tanggal' => now(), 'status' => $statusText],
                        '/pengajuanlabsdansubs',
                        $data->karyawan->nama_lengkap,
                        'Menyetujui Pengajuan Lab/Subscription',
                        $u->id
                    )
                );
            }

            return redirect()->route('pengajuanlabsdansubs.index')
                ->with('success', 'Akses Lab berhasil diberikan kepada ' . ($data->karyawan->nama_lengkap ?? 'peminta') . '!');
        }

        // CABANG 2: LAB BARU / EXPIRED -> PENGAJUAN BARANG ATAS NAMA TIM ITSM
        $jenisTransaksi = ($keputusan === 'pengajuan_barang_perpanjangan') ? 'pembaharuan' : 'baru';

        $existingPengajuanBarang = PengajuanBarang::where('tipe', 'Lab')
            ->where('id_kegiatan', $data->id)
            ->first();

        $statusFinance = "Telah disetujui oleh Koordinator ITSM dan sedang diproses oleh Finance";

        if (!$existingPengajuanBarang) {
            $pengajuanBarang = PengajuanBarang::create([
                'id_karyawan' => $itsmKaryawan->id,
                'id_kegiatan' => $data->id,
                'tipe'        => 'Lab',
            ]);

            detailPengajuanBarang::create([
                'id_pengajuan_barang' => $pengajuanBarang->id,
                'nama_barang'         => $lab->nama_labs . ($lab->merk ? " ({$lab->merk})" : ''),
                'qty'                 => 1,
                'harga'               => $hargaRupiah,
                'keterangan'          => 'Pengadaan ' . ucfirst($jenisTransaksi) . ' Lab oleh ' . $itsmKaryawan->nama_lengkap . ' (' . $itsmKaryawan->jabatan . ')',
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

            $trackLab = TrackingPengajuanLabSubs::create([
                'id_pengajuan_lab_subs' => $data->id,
                'tracking'              => "Dialihkan ke Pengajuan Barang oleh {$itsmKaryawan->nama_lengkap} ({$itsmKaryawan->jabatan})",
                'tanggal'               => now(),
            ]);

            $financeKar  = karyawan::where('jabatan', 'Finance & Accounting')->first();
            $targetCodes = [$data->kode_karyawan, $itsmKaryawan->kode_karyawan];
            if ($financeKar) {
                $targetCodes[] = $financeKar->kode_karyawan;
            }

            $notifUsers = User::whereHas('karyawan', fn($q) => $q->whereIn('kode_karyawan', array_unique($targetCodes)))->get();
            foreach ($notifUsers as $u) {
                NotificationFacade::send(
                    $u,
                    new ApprovalLabSubsNotification(
                        ['tanggal' => now(), 'status' => $statusFinance],
                        '/pengajuanlabsdansubs',
                        $itsmKaryawan->nama_lengkap,
                        'Menyetujui Pengajuan Lab/Subscription',
                        $u->id
                    )
                );
            }
        } else {
            detailPengajuanBarang::where('id_pengajuan_barang', $existingPengajuanBarang->id)->update([
                'nama_barang' => $lab->nama_labs . ($lab->merk ? " ({$lab->merk})" : ''),
                'harga'       => $hargaRupiah,
            ]);

            $trackLab = TrackingPengajuanLabSubs::create([
                'id_pengajuan_lab_subs' => $data->id,
                'tracking'              => "Data Teknis Lab diperbarui oleh {$itsmKaryawan->nama_lengkap}",
                'tanggal'               => now(),
            ]);
        }

        $data->update([
            'id_labs'         => $lab->id,
            'jenis_transaksi' => $jenisTransaksi,
            'lab_snapshot'    => $snapshotData,
            'id_tracking'     => $trackLab->id,
        ]);

        return redirect()->route('pengajuanlabsdansubs.index')
            ->with('success', 'Pengajuan Barang (Lab) atas nama ' . $itsmKaryawan->nama_lengkap . ' berhasil diteruskan ke Finance!');
    }

    public function show($id)
    {
        $data = PengajuanLabSubs::with(['karyawan', 'lab', 'tracking', 'rkm.perusahaan', 'rkm.materi'])
            ->findOrFail($id);

        $pengajuanBarang = PengajuanBarang::where('tipe', 'Lab')
            ->where('id_kegiatan', $data->id)
            ->with('karyawan')
            ->first();

        if ($pengajuanBarang) {
            $trackingBarang = tracking_pengajuan_barang::where('id_pengajuan_barang', $pengajuanBarang->id)->get();
            $mergedTracking = $data->tracking->concat($trackingBarang)->sortBy('tanggal')->values();
            $data->setRelation('tracking', $mergedTracking);
        }

        return view('pengajuanlabs.show', compact('data', 'pengajuanBarang'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'approval'       => 'required|string',
            'alasan'         => 'nullable|string|max:500',
            'finance_status' => 'nullable',
        ]);

        $data    = PengajuanLabSubs::with('karyawan')->findOrFail($id);
        $jabatan = auth()->user()->karyawan->jabatan;

        if ($jabatan === 'Finance & Accounting' && !in_array($request->approval, ['1', '2'])) {
            $status = $request->approval;

            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status pencairan wajib dipilih.'
                ], 400);
            }

            $e = TrackingPengajuanLabSubs::create([
                'id_pengajuan_lab_subs' => $id,
                'tracking'              => $status,
                'tanggal'               => now()
            ]);

            $data->update(['id_tracking' => $e->id]);

            if ($status === 'Pencairan Sudah Selesai') {
                $final = TrackingPengajuanLabSubs::create([
                    'id_pengajuan_lab_subs' => $id,
                    'tracking'              => 'Selesai',
                    'tanggal'               => now()->addSeconds(1)
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
                        '/pengajuanlabsdansubs',
                        $data->karyawan->nama_lengkap,
                        'Update Status Pencairan oleh Finance',
                        $user->id
                    )
                );
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Status pencairan berhasil diperbarui!',
                'redirect' => route('pengajuanlabsdansubs.index')
            ]);
        }

        if ($request->approval == '1') {
            if (!in_array($jabatan, ['Education Manager', 'SPV Sales', 'GM'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Untuk Koordinator ITSM / Technical Support, silakan gunakan tombol Proses Lab.'
                ], 403);
            }

            $status = "Telah disetujui oleh {$jabatan} dan sedang ditinjau oleh Koordinator ITSM";

            $e = TrackingPengajuanLabSubs::create([
                'id_pengajuan_lab_subs' => $id,
                'tracking'              => $status,
                'tanggal'               => now()
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
                        '/pengajuanlabsdansubs',
                        $data->karyawan->nama_lengkap,
                        'Menyetujui Pengajuan Lab/Subscription',
                        $user->id
                    )
                );
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Permintaan Lab disetujui dan diteruskan ke Management Lab (ITSM)!',
                'redirect' => route('pengajuanlabsdansubs.index')
            ]);
        }

        if ($request->approval == '2') {
            $alasan = $request->alasan ?? 'Tidak disebutkan';
            $status = "Pengajuan ditolak oleh {$jabatan} karena {$alasan}";

            $e = TrackingPengajuanLabSubs::create([
                'id_pengajuan_lab_subs' => $id,
                'tracking'              => $status,
                'tanggal'               => now()
            ]);

            $data->update(['id_tracking' => $e->id]);

            $userObjs = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $data->karyawan->kode_karyawan))->get();

            foreach ($userObjs as $user) {
                NotificationFacade::send(
                    $user,
                    new ApprovalLabSubsNotification(
                        ['tanggal' => now(), 'status' => $status],
                        '/pengajuanlabsdansubs',
                        $data->karyawan->nama_lengkap,
                        'Menolak Pengajuan Lab/Subscription',
                        $user->id
                    )
                );
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Pengajuan berhasil ditolak!',
                'redirect' => route('pengajuanlabsdansubs.index')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada aksi yang diproses!'
        ], 400);
    }

    public function destroy($id)
    {
        $data = PengajuanLabSubs::findOrFail($id);
        TrackingPengajuanLabSubs::where('id_pengajuan_lab_subs', $data->id)->delete();
        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permintaan lab berhasil dihapus.'
        ]);
    }

    public function uploadInvoice(Request $request, $id)
    {
        $request->validate([
            'invoice' => 'required|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = PengajuanLabSubs::findOrFail($id);

        if ($data->invoice && Storage::exists('public/pengajuanlabsubs/' . $data->invoice)) {
            Storage::delete('public/pengajuanlabsubs/' . $data->invoice);
        }

        $filename = 'invoice_' . $id . '_' . time() . '.' . $request->file('invoice')->getClientOriginalExtension();
        $request->file('invoice')->storeAs('public/pengajuanlabsubs', $filename);

        $data->update(['invoice' => $filename]);

        $pengajuanBarang = PengajuanBarang::where('tipe', 'Lab')->where('id_kegiatan', $data->id)->first();
        if ($pengajuanBarang) {
            $pengajuanBarang->update(['invoice' => 'pengajuanlabsubs/' . $filename]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil diunggah!',
            'file'    => asset('storage/pengajuanlabsubs/' . $filename),
        ]);
    }

    public function exportPDF($id)
    {
        $data = PengajuanLabSubs::with(['lab', 'karyawan', 'tracking'])->findOrFail($id);

        $labSnapshot = null;
        if (!empty($data->lab_snapshot)) {
            $labSnapshot = is_string($data->lab_snapshot) ? json_decode($data->lab_snapshot) : (object) $data->lab_snapshot;
        } elseif ($data->lab) {
            $labSnapshot = (object) $data->lab->toArray();
        }

        $subsSnapshot = null;

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

        return view('exports.pengajuan_labsubs-pdf', compact('data', 'finance', 'gm', 'labSnapshot', 'subsSnapshot'));
    }

    public function updateMasterLab(Request $request, $id)
    {
        $request->validate([
            'nama_labs'        => 'required|string|max:255',
            'merk'             => 'nullable|string|max:255',
            'tipe'             => 'nullable|in:subscription,one-time',
            'status'           => 'nullable|in:active,pending,expired',
            'harga_rupiah'     => 'nullable|numeric',
            'materi_ids'       => 'nullable|array',
            'materi_ids.*'     => 'exists:materis,id'
        ]);

        $lab = Lab::findOrFail($id);

        $lab->update([
            'nama_labs'        => $request->nama_labs,
            'merk'             => $request->merk,
            'tipe'             => $request->tipe,
            'duration_minutes' => $request->duration_minutes,
            'status'           => $request->status,
            'is_active'        => ($request->status === 'active'),
            'desc'             => $request->desc ?? $request->deskripsi,
            'lab_url'          => $request->lab_url ?? $request->url_labs,
            'access_code'      => $request->access_code ?? $request->kode_akses,
            'start_date'       => $request->start_date ?? $request->tanggal_mulai,
            'end_date'         => $request->end_date ?? $request->tanggal_berakhir,
            'mata_uang'        => $request->mata_uang,
            'harga'            => $request->harga ?? $request->nominal_harga_asli,
            'kurs'             => $request->kurs,
            'harga_rupiah'     => $request->harga_rupiah,
        ]);

        if ($request->has('materi_ids')) {
            $lab->materis()->sync($request->materi_ids);
        } else {
            $lab->materis()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Data lab dan materi berhasil diperbarui',
            'data'    => $lab
        ]);
    }

    public function renewLab($id)
    {
        $user         = auth()->user();
        $itsmKaryawan = $user->karyawan;

        $lab = Lab::findOrFail($id);

        $pengajuan = PengajuanLabSubs::create([
            'kode_karyawan'   => $itsmKaryawan->kode_karyawan,
            'id_labs'         => $lab->id,
            'id_rkm'          => null,
            'jenis_transaksi' => 'pembaharuan',
            'lab_snapshot'    => array_merge($lab->toArray(), [
                'handled_by'         => $itsmKaryawan->nama_lengkap,
                'handled_by_jabatan' => $itsmKaryawan->jabatan,
            ]),
        ]);

        $trackingText = 'Pengajuan Pembaharuan Lab Diajukan oleh ' . $itsmKaryawan->nama_lengkap . ' - Silakan lengkapi harga & teruskan ke Finance';

        $trackingModel = TrackingPengajuanLabSubs::create([
            'id_pengajuan_lab_subs' => $pengajuan->id,
            'tracking'              => $trackingText,
            'tanggal'               => now(),
        ]);

        $pengajuan->update(['id_tracking' => $trackingModel->id]);

        return response()->json([
            'success' => true,
            'message' => 'Draft perpanjangan lab ' . $lab->nama_labs . ' berhasil dibuat! Silakan klik Proses Lab pada tabel untuk meneruskan ke Finance.'
        ]);
    }
}