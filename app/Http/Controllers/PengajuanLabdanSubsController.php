<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\karyawan;
use App\Models\jabatan;
use App\Models\User;
use App\Models\RKM;
use App\Models\Materi;
use App\Models\Lab;
use App\Models\Subscription;
use App\Models\PengajuanLabSubs;
use App\Models\TrackingPengajuanLabSubs;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\PengajuanLabdanSubsNotification;
use App\Notifications\ApprovalLabSubsNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class PengajuanLabdanSubsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user || !$user->karyawan) {
            return view('auth.login');
        }

        $jabatan = $user->karyawan->jabatan;

        $jabatanBuka = ['Finance & Accounting', 'GM', 'SPV Sales', 'Koordinator ITSM', 'Technical Support', 'Instruktur'];

        if (in_array($jabatan, $jabatanBuka)) {
            $tracking = 'buka';
        } else {
            $karyawan = $user->karyawan->nama_lengkap;

            $trackingRecord = TrackingPengajuanLabSubs::whereHas('pengajuan.karyawan', function ($query) use ($karyawan) {
                    $query->where('nama_lengkap', $karyawan);
                })
                ->latest()
                ->first();

            $tracking = $this->determineTrackingStatus($trackingRecord);
        }

        $materis = Materi::all();

        return view('pengajuanlabs.index', compact('tracking', 'materis'));
    }

    private function determineTrackingStatus($trackingRecord)
    {
        if (is_null($trackingRecord)) {
            return 'buka';
        }

        $pengajuan = $trackingRecord->pengajuan;

        // if (str_contains($trackingRecord->tracking, 'Selesai')) {
        //     return 'buka';
        // }

        // if (empty($pengajuan?->invoice)) {
        //     return 'tutup';
        // }

        return 'buka';
    }

    public function getPengajuanLabSubs($month, $year)
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = Karyawan::findOrFail($user);
        $jabatan = $karyawan->jabatan;
        $divisi = $karyawan->divisi;

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

        return response()->json([
            'success' => true,
            'message' => 'List Pengajuan Lab',
            'data'    => $Pengajuan,
        ]);
    }

    public function getMasterLabs()
    {
        $labs = Lab::with('materis')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $labs
        ]);
    }

    public function edit($id)
    {
        $user = auth()->user();
        $jabatan = $user->karyawan->jabatan;

        // 1. Cek Hak Akses
        if (!in_array($jabatan, ['Technical Support', 'Koordinator ITSM'])) {
            return redirect()->route('pengajuanlabsdansubs.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit data teknis.');
        }

        $data = PengajuanLabSubs::with(['lab'])->findOrFail($id);

        return view('pengajuanlabs.edit', compact('data'));
    }


    public function create()
    {
        $user = auth()->user();

        $karyawan = $user->karyawan;

        $rkms = RKM::with(['materi', 'perusahaan'])
            ->whereDate('tanggal_awal', '>=', now())
            ->orderBy('tanggal_awal', 'asc')
            ->get();

        return view('pengajuanlabs.create', compact('karyawan', 'rkms'));
    }

    public function getLabsByRkm($rkmId)
    {
        $rkm = RKM::with('materi')->find($rkmId);

        if (!$rkm || !$rkm->materi) {
            return response()->json(['materi_nama' => '', 'labs' => []]);
        }

        // Ambil Lab yang SUDAH terhubung ke materi ini (via Pivot) DAN statusnya Active
        $labs = $rkm->materi->labs()
                    ->where('labs.status', 'Active')
                    ->where('labs.is_active', true)
                    ->get();

        return response()->json([
            'materi_nama' => $rkm->materi->nama_materi,
            'labs' => $labs
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        // Pastikan nama tabel 'rkm' sesuai dengan database Anda (singular/plural)
        $request->validate([
            'id_rkm' => 'required|exists:r_k_m_s,id',
            'sumber_lab' => 'required|in:existing,new',
        ]);

        $karyawan = \App\Models\karyawan::where('kode_karyawan', $request->kode_karyawan)->firstOrFail();

        $labId = null;
        $jenisTransaksi = 'existing';
        $namaLabNotification = ''; // Variabel bantu untuk nama lab di notifikasi
        $descLabNotification = '';

        // Status Tracking Awal: Selalu ke Education Manager dulu
        $trackingText = 'Diajukan dan Sedang Ditinjau oleh Education Manager';

        // --- LOGIC A: MENGGUNAKAN LAB EXISTING ---
        if ($request->sumber_lab === 'existing') {
            $request->validate(['id_existing_lab' => 'required|exists:labs,id']);

            $labId = $request->id_existing_lab;
            $jenisTransaksi = 'existing';

            // Ambil nama lab untuk notifikasi
            $existingLab = Lab::find($labId);
            $namaLabNotification = $existingLab->nama_labs;
            $descLabNotification = $existingLab->desc;
        }

        else {
            $request->validate([
                'new_nama_labs' => 'required|string|max:255',
                'new_merk'      => 'required|string|max:255',
            ]);

            $newLab = Lab::create([
                'kode_karyawan' => $karyawan->kode_karyawan,
                'nama_labs'     => $request->new_nama_labs,
                'merk'          => $request->new_merk,
                'tipe'          => 'one-time', // Default, nanti diupdate Technical Support
                'desc'          => 'Request Baru oleh Divisi Education',
                'lab_url'       => null,
                'status'        => 'pending',
                'is_active'     => false,
            ]);

            $labId = $newLab->id;
            $jenisTransaksi = 'baru';

            $namaLabNotification = $request->new_nama_labs;
            $descLabNotification = 'Request Lab Baru';
        }

        // 2. Simpan Data Pengajuan
        $pengajuan = PengajuanLabSubs::create([
            'kode_karyawan'   => $karyawan->kode_karyawan,
            'id_labs'         => $labId,
            'id_rkm'          => $request->id_rkm,
            'jenis_transaksi' => $jenisTransaksi,
        ]);

        // 3. Simpan Tracking
        $trackingModel = TrackingPengajuanLabSubs::create([
            'id_pengajuan_lab_subs' => $pengajuan->id,
            'tracking'              => $trackingText,
            'tanggal'               => now(),
        ]);

        $pengajuan->update(['id_tracking' => $trackingModel->id]);

        // Cari user dengan jabatan Education Manager
        $eduman = User::whereHas('karyawan', function($q) {
            $q->where('jabatan', 'Education Manager');
        })->first();

        if ($eduman) {
            // Ambil data RKM untuk detail notifikasi
            $rkm = RKM::with(['materi', 'perusahaan'])->find($request->id_rkm);

            $notifData = [
                'id_karyawan'       => $karyawan->id,
                'tanggal_pengajuan' => now(),
                'jenis_pengajuan'   => 'lab',
                'nama'              => $namaLabNotification,
                'deskripsi'         => $descLabNotification,
                'rkm'               => [
                    'nama_materi'     => $rkm->materi->nama_materi ?? '-',
                    'nama_perusahaan' => $rkm->perusahaan->nama_perusahaan ?? '-',
                    'tanggal_mulai'   => $rkm->tanggal_awal,
                    'tanggal_selesai' => $rkm->tanggal_akhir,
                ]
            ];

            $path = "/pengajuanlabsdansubs";
            $type = "Mengajukan Lab (" . ucfirst($jenisTransaksi) . ")";

            // Kirim Notif
            NotificationFacade::send($eduman, new PengajuanLabdanSubsNotification($notifData, $path, $type, $eduman->id));
        }

        return redirect()->route('pengajuanlabsdansubs.index')
            ->with('success', 'Pengajuan berhasil dikirim dan menunggu persetujuan Education Manager.');
    }

    public function show($id)
    {
        $data = PengajuanLabSubs::with(['karyawan', 'lab','tracking', 'rkm.perusahaan', 'rkm.materi'])
            ->findOrFail($id);

        return view('pengajuanlabs.show', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'approval' => 'required|string',
            'alasan' => 'nullable|string|max:500',
            'finance_status' => 'nullable',
        ]);

        $data = PengajuanLabSubs::with('karyawan')->findOrFail($id);
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
                'tracking' => $status,
                'tanggal' => now()
            ]);

            $data->update(['id_tracking' => $e->id]);

            if ($status === 'Pencairan Sudah Selesai') {
                $final = TrackingPengajuanLabSubs::create([
                    'id_pengajuan_lab_subs' => $id,
                    'tracking' => 'Selesai',
                    'tanggal' => now()->addSeconds(1)
                ]);

                $data->update(['id_tracking' => $final->id]);
            }

            $userObjs = User::whereHas('karyawan', function ($q) use ($data) {
                $q->where('kode_karyawan', $data->karyawan->kode_karyawan);
            })->get();

            $notifData = [
                'tanggal' => now(),
                'status' => $status,
            ];

            foreach ($userObjs as $user) {
                NotificationFacade::send(
                    $user,
                    new ApprovalLabSubsNotification(
                        $notifData,
                        '/pengajuanlabsdansubs',
                        $data->karyawan->nama_lengkap,
                        'Update Status Pencairan oleh Finance',
                        $user->id
                    )
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Status pencairan berhasil diperbarui!',
                'redirect' => route('pengajuanlabsdansubs.index')
            ]);
        }

        if ($request->approval == '1') {
            $status = '';
            $nextUser = null;
            $updatePayload = [];

            switch ($jabatan) {
                case 'SPV Sales':
                case 'Education Manager':
                case 'GM':
                    $status = "Telah disetujui oleh {$jabatan} dan sedang ditinjau oleh Koordinator ITSM";
                    $nextUser = Karyawan::where('jabatan', 'Koordinator ITSM')->first();

                    $e = TrackingPengajuanLabSubs::create([
                        'id_pengajuan_lab_subs' => $id,
                        'tracking' => $status,
                        'tanggal'  => now()
                    ]);
                    $updatePayload['id_tracking'] = $e->id;
                    break;

                case 'Koordinator ITSM':
                    // Logic Snapshot Data
                    if ($data->id_labs) {
                        $labData = \App\Models\Lab::find($data->id_labs);
                        if ($labData) $updatePayload['lab_snapshot'] = $labData->toArray();
                    } elseif ($data->id_subs) {
                        $subsData = \App\Models\Subscription::find($data->id_subs);
                        if ($subsData) $updatePayload['subs_snapshot'] = $subsData->toArray();
                    }

                    // PERBAIKAN: Pisahkan jalur untuk Existing Asset dan Pengadaan Baru
                    if ($data->jenis_transaksi === 'existing') {
                        // Jalur Existing Asset -> Langsung Selesai tanpa ke Finance
                        $status = "Telah disetujui oleh Koordinator ITSM dan lihat akses nya di Detail";
                        $nextUser = null; // Tidak perlu dinotifikasi ke pihak lain, cukup pengaju

                        $e = TrackingPengajuanLabSubs::create([
                            'id_pengajuan_lab_subs' => $id,
                            'tracking' => $status,
                            'tanggal'  => now()
                        ]);

                        // Tambah tracking 'Selesai' agar otomatis pindah ke tabel Riwayat Selesai
                        $final = TrackingPengajuanLabSubs::create([
                            'id_pengajuan_lab_subs' => $id,
                            'tracking' => 'Selesai',
                            'tanggal'  => now()->addSeconds(1)
                        ]);

                        $updatePayload['id_tracking'] = $final->id;
                    } else {
                        // Jalur Pengadaan Baru -> Lanjut ke Finance
                        $status = "Telah disetujui oleh Koordinator ITSM dan sedang diproses oleh Finance";
                        $nextUser = Karyawan::where('jabatan', 'Finance & Accounting')->first();

                        $e = TrackingPengajuanLabSubs::create([
                            'id_pengajuan_lab_subs' => $id,
                            'tracking' => $status,
                            'tanggal'  => now()
                        ]);
                        $updatePayload['id_tracking'] = $e->id;
                    }
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Role Anda tidak memiliki hak approval!'
                    ], 403);
            }

            $e = TrackingPengajuanLabSubs::create([
                'id_pengajuan_lab_subs' => $id,
                'tracking' => $status,
                'tanggal'  => now()
            ]);

            $updatePayload['id_tracking'] = $e->id;
            $data->update($updatePayload);

            // Notifikasi ke Pengaju & Next User
            $usersCodes = [$data->karyawan->kode_karyawan];
            if ($nextUser) $usersCodes[] = $nextUser->kode_karyawan;

            $userObjs = User::whereHas('karyawan', fn($q) => $q->whereIn('kode_karyawan', $usersCodes))->get();

            $notifData = [
                'tanggal' => now(),
                'status'  => $status,
            ];

            foreach ($userObjs as $user) {
                NotificationFacade::send(
                    $user,
                    new ApprovalLabSubsNotification(
                        $notifData,
                        '/pengajuanlabsdansubs',
                        $data->karyawan->nama_lengkap,
                        'Menyetujui Pengajuan Lab/Subscription',
                        $user->id
                    )
                );
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Approval berhasil disimpan!',
                'redirect' => route('pengajuanlabsdansubs.index')
            ]);
        }

        if ($request->approval == '2') {
            $alasan = $request->alasan ?? 'Tidak disebutkan';
            $status = "Pengajuan ditolak oleh {$jabatan} karena {$alasan}";

            $e = TrackingPengajuanLabSubs::create([
                'id_pengajuan_lab_subs' => $id,
                'tracking' => $status,
                'tanggal' => now()
            ]);

            $data->update(['id_tracking' => $e->id]);

            $userObjs = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $data->karyawan->kode_karyawan))->get();

            $notifData = [
                'tanggal' => now(),
                'status' => $status,
            ];

            foreach ($userObjs as $user) {
                NotificationFacade::send(
                    $user,
                    new ApprovalLabSubsNotification(
                        $notifData,
                        '/pengajuanlabsdansubs',
                        $data->karyawan->nama_lengkap,
                        'Menolak Pengajuan Lab/Subscription',
                        $user->id
                    )
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil ditolak!',
                'redirect' => route('pengajuanlabsdansubs.index')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada aksi yang diproses!'
        ], 400);
    }

    public function updateLabSubs(Request $request, $id)
    {
        $data = PengajuanLabSubs::with('lab')->findOrFail($id);

        $validated = $request->validate([
            'nama_labs'    => 'required|string|max:255',
            'merk'         => 'nullable|string|max:255',
            'tipe'         => 'required|in:one-time,subscription',
            'desc'         => 'nullable|string',
            'lab_url'      => 'nullable|url',
            'access_code'  => 'nullable|string|max:255',
            'duration_minutes' => 'nullable|numeric',
            'mata_uang'    => 'required|string|max:50',
            'harga'        => 'required|numeric',
            'kurs'         => 'nullable|numeric',
            'harga_rupiah' => 'nullable|string',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date',
            'status'       => 'required|string|in:active,pending,expired',
        ]);

        if (!empty($validated['harga_rupiah'])) {
            $validated['harga_rupiah'] = (int) preg_replace('/[^\d]/', '', $validated['harga_rupiah']);
        } else {
            $kurs = $validated['kurs'] ?? 1;
            $validated['harga_rupiah'] = $validated['harga'] * $kurs;
        }
        if ($validated['mata_uang'] === 'Rupiah') {
            $validated['kurs'] = 1;
            $validated['harga_rupiah'] = $validated['harga'];
        }

        if ($data->lab) {
            $data->lab->update($validated);
        }

        return redirect()
            ->route('pengajuanlabsdansubs.index')
            ->with('success', 'Data Teknis Lab berhasil diperbarui!');
    }

    public function uploadInvoice(Request $request, $id)
    {
        $request->validate([
            'invoice' => 'required|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = PengajuanLabSubs::findOrFail($id);

        // Hapus invoice lama jika ada
        if ($data->invoice && Storage::exists('public/pengajuanlabsubs/' . $data->invoice)) {
            Storage::delete('public/pengajuanlabsubs/' . $data->invoice);
        }

        // Simpan file baru
        $filename = 'invoice_' . $id . '_' . time() . '.' . $request->file('invoice')->getClientOriginalExtension();
        $request->file('invoice')->storeAs('public/pengajuanlabsubs', $filename);

        // Update data
        $data->update(['invoice' => $filename]);

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil diunggah!',
            'file' => asset('storage/pengajuanlabsubs/' . $filename),
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
        if (!empty($data->subs_snapshot)) {
            $subsSnapshot = is_string($data->subs_snapshot) ? json_decode($data->subs_snapshot) : (object) $data->subs_snapshot;
        } elseif ($data->subs) {
            $subsSnapshot = (object) $data->subs->toArray();
        }

        // 2. Tentukan siapa yang "menyetujui" berdasarkan divisi
        if ($data->karyawan->divisi == 'Education') {
            $finance = Karyawan::where('jabatan', 'Education Manager')->latest()->first();
        } elseif ($data->karyawan->divisi == 'Sales & Marketing') {
            $finance = Karyawan::where('jabatan', 'SPV Sales')->latest()->first();
        } elseif ($data->karyawan->divisi == 'Office') {
            $finance = Karyawan::where('jabatan', 'GM')->latest()->first();
        } elseif ($data->karyawan->divisi == 'IT Service Management') {
            $finance = Karyawan::where('jabatan', 'Koordinator ITSM')->latest()->first();
        } else {
            $finance = null;
        }

        $gm = Karyawan::where('jabatan', 'GM')->latest()->first();

        // 3. Kirim variabel snapshot ke view
        return view('exports.pengajuan_labsubs-pdf', compact('data', 'finance', 'gm', 'labSnapshot', 'subsSnapshot'));
    }

    public function updateMasterLab(Request $request, $id)
    {
        $request->validate([
            'nama_labs' => 'required|string|max:255',
            'merk' => 'nullable|string|max:255',
            'tipe' => 'nullable|in:subscription,one-time',
            // Koreksi nilai parameter in menjadi huruf kecil sesuai dengan value HTML
            'status' => 'nullable|in:active,pending,expired',
            'harga_rupiah' => 'nullable|numeric',
            'materi_ids' => 'nullable|array',
            'materi_ids.*' => 'exists:materis,id'
        ]);

        $lab = Lab::findOrFail($id);

        $lab->update($request->except('materi_ids'));

        if ($request->has('materi_ids')) {
            $lab->materis()->sync($request->materi_ids);
        } else {
            $lab->materis()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Data lab dan materi berhasil diperbarui',
            'data' => $lab
        ]);
    }

    public function renewLab($id)
    {
        $user = auth()->user();
        $karyawan = $user->karyawan;

        $lab = Lab::findOrFail($id);

        $pengajuan = PengajuanLabSubs::create([
            'kode_karyawan'   => $karyawan->kode_karyawan,
            'id_labs'         => $lab->id,
            'id_rkm'          => null,
            'jenis_transaksi' => 'pembaharuan',
        ]);

        $trackingText = 'Pengajuan Pembaharuan Lab Diajukan dan Sedang Ditinjau oleh Koordinator ITSM';

        $trackingModel = TrackingPengajuanLabSubs::create([
            'id_pengajuan_lab_subs' => $pengajuan->id,
            'tracking'              => $trackingText,
            'tanggal'               => now(),
        ]);

        $pengajuan->update(['id_tracking' => $trackingModel->id]);

        $koor = User::whereHas('karyawan', function($q) {
            $q->where('jabatan', 'Koordinator ITSM');
        })->first();

        if ($koor) {
            $notifData = [
                'tanggal' => now(),
                'status'  => $trackingText,
            ];
            NotificationFacade::send(
                $koor,
                new ApprovalLabSubsNotification(
                    $notifData,
                    '/pengajuanlabsdansubs',
                    $karyawan->nama_lengkap,
                    'Pengajuan Pembaharuan Lab: ' . $lab->nama_labs,
                    $koor->id
                )
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan pembaharuan untuk lab ' . $lab->nama_labs . ' berhasil dibuat!'
        ]);
    }

}
