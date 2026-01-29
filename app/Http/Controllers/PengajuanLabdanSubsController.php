<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\karyawan;
use App\Models\jabatan;
use App\Models\User;
use App\Models\RKM;
use App\Models\Lab;
use App\Models\Subscription;
use App\Models\PengajuanLabSubs;
use App\Models\TrackingPengajuanLabSubs;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\PengajuanLabdanSubsNotification;
use App\Notifications\ApprovalLabSubsNotification;
use Carbon\Carbon;


class PengajuanLabdanSubsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user || !$user->karyawan) {
            return view('auth.login');
        }

        $jabatan = $user->karyawan->jabatan;

        // Daftar jabatan yg otomatis bisa ajukan
        $jabatanBuka = ['Finance & Accounting', 'GM', 'SPV Sales', 'Koordinator ITSM'];

        if (in_array($jabatan, $jabatanBuka)) {
            $tracking = 'buka';
        } else {
            $karyawan = $user->karyawan->nama_lengkap;

            $trackingRecord = TrackingPengajuanLabSubs::with(['pengajuan.karyawan'])
                ->whereHas('pengajuan.karyawan', function ($query) use ($karyawan) {
                    $query->where('nama_lengkap', $karyawan);
                })
                ->latest()
                ->first();

            $tracking = $this->determineTrackingStatus($trackingRecord);
        }
        // dd($tracking);

        return view('pengajuanlabs.index', compact('tracking'));
    }

    private function determineTrackingStatus($trackingRecord)
    {
        // Jika tidak ada record, buka
        if (is_null($trackingRecord)) {
            return 'buka';
        }

        $pengajuan = $trackingRecord->pengajuanbarang;

        // Jika tracking sudah selesai pencairan, tutup
        if ($trackingRecord->tracking === 'Pencairan Sudah Selesai') {
            return 'tutup';
        } else {
            return 'buka';
        }

        // Jika invoice tidak ada, tutup
        if (empty($pengajuan?->invoice)) {
            return 'tutup';
        }

        // Jika invoice ada dan tidak kosong, buka
        if (!empty($pengajuan?->invoice)) {
            return 'buka';
        }

        // Default buka
        return 'buka';
    }

    public function getPengajuanLabSubs($month, $year)
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = Karyawan::findOrFail($user);
        $jabatan = $karyawan->jabatan;
        $divisi = $karyawan->divisi;

        if (in_array($jabatan, ['Finance & Accounting', 'Koordinator ITSM', 'GM', 'Technical Support', 'Education Manager'])) {
            $Pengajuan = PengajuanLabSubs::with(['karyawan', 'tracking', 'lab', 'subs', 'rkm.perusahaan', 'rkm.materi'])
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->latest()
                ->get();
        } elseif (in_array($jabatan, ['Office Manager', 'Education Manager', 'SPV Sales'])) {
            $Pengajuan = PengajuanLabSubs::with(['karyawan', 'tracking', 'lab', 'subs', 'rkm.perusahaan', 'rkm.materi'])
                ->whereHas('karyawan', function ($q) use ($divisi) {
                    $q->where('divisi', $divisi);
                })
                ->latest()
                ->get();
        } elseif (in_array($jabatan, ['GM', 'Koordinator Office'])) {
            $Pengajuan = PengajuanLabSubs::with(['karyawan', 'tracking', 'lab', 'subs', 'rkm.perusahaan', 'rkm.materi'])
                ->latest()
                ->get();
        } else {
            $Pengajuan = PengajuanLabSubs::with(['karyawan', 'tracking', 'lab', 'subs', 'rkm.perusahaan', 'rkm.materi'])
                ->where('kode_karyawan', $karyawan->kode_karyawan)
                ->latest()
                ->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'List Pengajuan Lab & Subscription',
            'data'    => $Pengajuan,
        ]);
    }

    public function edit($id)
    {
        // Ambil data pengajuan beserta relasinya
        $data = PengajuanLabSubs::with(['subs', 'lab'])
            ->findOrFail($id);


        // Kirim data ke view
        return view('pengajuanlabs.edit', [
            'data' => $data
        ]);
    }


    public function create()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = Karyawan::findOrFail($user);

        // Ambil tanggal hari ini dan 1 bulan ke depan
        $today = Carbon::now();
        $nextMonth = Carbon::now()->addMonth();

        // Ambil data RKM yang jadwalnya dalam 1 bulan ke depan
        $rkms = RKM::with(['perusahaan', 'materi'])
            ->whereBetween('tanggal_awal', [$today, $nextMonth])
            ->orWhereBetween('tanggal_akhir', [$today, $nextMonth])
            ->get();

        // Ambil data Subscription (subs) untuk karyawan ini
        $subs = Subscription::get();

        // Ambil data Lab (labs) untuk karyawan ini
        $labs = Lab::get();

        return view('pengajuanlabs.create', compact('karyawan', 'rkms', 'subs', 'labs'));
    }

    public function store(Request $request)
    {
        $rules = [
            'kode_karyawan'   => 'required|string',
            'jenis_pengajuan' => 'required|in:lab,subs',
        ];

        // Validasi sesuai jenis pengajuan
        if ($request->jenis_pengajuan === 'lab') {
            $rules['id_rkm'] = 'required|integer';
            $rules['lab_id'] = 'required';

            if ($request->lab_id === 'new') {
                $rules['new_nama_labs'] = 'required|string|max:255';
                $rules['new_desc_labs'] = 'nullable|string';
            }
            $rules['lab_keterangan'] = 'nullable|string';
        }

        if ($request->jenis_pengajuan === 'subs') {
            $rules['id_rkm'] = 'nullable|integer';
            $rules['subs_id'] = 'required';

            if ($request->subs_id === 'new') {
                $rules['new_nama_subs'] = 'required|string|max:255';
                $rules['new_merk']      = 'required|string|max:255';
                $rules['new_desc_subs'] = 'nullable|string';
            }
            $rules['subs_keterangan'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        $karyawan = Karyawan::where('kode_karyawan', $request->kode_karyawan)->firstOrFail();

        // Tentukan teks tracking berdasarkan divisi
        switch ($karyawan->divisi) {
            case 'Education':
                $tracking = 'Diajukan dan Sedang Ditinjau oleh Education Manager';
                break;
            case 'Office':
                $tracking = 'Diajukan dan Sedang Ditinjau oleh GM';
                break;
            case 'Sales & Marketing':
                $tracking = 'Diajukan dan Sedang Ditinjau oleh SPV Sales';
                break;
            case 'IT Service Management':
                $tracking = 'Diajukan dan Sedang Ditinjau oleh Koordinator ITSM';
                break;
            default:
                $tracking = 'Diajukan dan Sedang Ditinjau';
        }

        if ($request->jenis_pengajuan === 'lab') {
            if ($request->lab_id === 'new') {
                $lab = Lab::create([
                    'kode_karyawan' => $request->kode_karyawan,
                    'nama_labs'     => $request->new_nama_labs,
                    'desc'          => $request->new_desc_labs,
                ]);
                $labId = $lab->id;
            } else {
                $labId = $request->lab_id;
            }

            $pengajuan = PengajuanLabSubs::create([
                'kode_karyawan' => $karyawan->kode_karyawan,
                'id_labs'       => $labId,
                'id_subs'       => null,
                'id_rkm'        => $request->id_rkm,
            ]);

            $trackingModel = TrackingPengajuanLabSubs::create([
                'id_pengajuan_lab_subs' => $pengajuan->id,
                'tracking'              => $tracking,
                'tanggal'               => now(),
            ]);

            $pengajuan->update(['id_tracking' => $trackingModel->id]);
            $type = "Mengajukan Lab";
            $path = "/pengajuanlabsdansubs";

        } elseif ($request->jenis_pengajuan === 'subs') {
            if ($request->subs_id === 'new') {
                $subs = Subscription::create([
                    'kode_karyawan' => $request->kode_karyawan,
                    'nama_subs'     => $request->new_nama_subs,
                    'merk'          => $request->new_merk,
                    'desc'          => $request->new_desc_subs,
                ]);
                $subsId = $subs->id;
            } else {
                $subsId = $request->subs_id;
            }

            $pengajuan = PengajuanLabSubs::create([
                'kode_karyawan' => $karyawan->kode_karyawan,
                'id_labs'       => null,
                'id_subs'       => $subsId,
                'id_rkm'        => $request->id_rkm ?: null,
            ]);

            $trackingModel = TrackingPengajuanLabSubs::create([
                'id_pengajuan_lab_subs' => $pengajuan->id,
                'tracking'              => $tracking,
                'tanggal'               => now(),
            ]);

            $pengajuan->update(['id_tracking' => $trackingModel->id]);
            $type = "Mengajukan Subscription";
            $path = "/pengajuanlabsdansubs";
        }

        $usersToNotify = [];

        // Ambil user berdasarkan jabatan
        $GM        = Karyawan::where('jabatan', 'GM')->first();
        $SPVSales  = Karyawan::where('jabatan', 'SPV Sales')->first();
        $Eduman    = Karyawan::where('jabatan', 'Education Manager')->first();
        $KoorITSM  = Karyawan::where('jabatan', 'Koordinator ITSM')->first();

        if ($request->jenis_pengajuan === 'lab') {
            $usersToNotify[] = $Eduman?->kode_karyawan;
        } elseif ($request->jenis_pengajuan === 'subs') {
            // Pengajuan subs → Atasan sesuai divisi
            switch ($karyawan->divisi) {
                case 'Education':
                    $usersToNotify[] = $Eduman?->kode_karyawan;
                    break;
                case 'Sales & Marketing':
                    $usersToNotify[] = $SPVSales?->kode_karyawan;
                    break;
                case 'Office':
                    $usersToNotify[] = $GM?->kode_karyawan;
                    break;
                case 'IT Service Management':
                    $usersToNotify[] = $KoorITSM?->kode_karyawan;
                    break;
            }
        }

        $users = User::whereHas('karyawan', function ($q) use ($usersToNotify) {
            $q->whereIn('kode_karyawan', array_filter($usersToNotify));
        })->get();

        // Data tambahan untuk notifikasi
        $data = [
            'id_karyawan'       => $karyawan->id_karyawan,
            'tanggal_pengajuan' => now(),
            'jenis_pengajuan'   => $request->jenis_pengajuan,
        ];

        if ($request->jenis_pengajuan === 'lab') {
            $lab = ($request->lab_id === 'new') ? $lab : Lab::find($labId);
            $data['nama'] = $lab?->nama_labs;
            $data['deskripsi'] = $lab?->desc;
        } elseif ($request->jenis_pengajuan === 'subs') {
            $subs = ($request->subs_id === 'new') ? $subs : Subscription::find($subsId);
            $data['nama'] = $subs?->nama_subs;
            $data['deskripsi'] = $subs?->desc;
        }

        if (!empty($request->id_rkm)) {
            $rkm = RKM::with(['materi', 'perusahaan'])->find($request->id_rkm);
            if ($rkm) {
                $data['rkm'] = [
                    'nama_materi'     => $rkm->materi?->nama_materi,
                    'nama_perusahaan' => $rkm->perusahaan?->nama_perusahaan,
                    'tanggal_mulai'   => $rkm->tanggal_mulai,
                    'tanggal_selesai' => $rkm->tanggal_selesai,
                ];
            }
        }

        foreach ($users as $user) {
            $receiverId = $user->id;
            NotificationFacade::send($user, new PengajuanLabdanSubsNotification($data, $path, $type, $receiverId));
        }

        return redirect()
            ->route('pengajuanlabsdansubs.index')
            ->with('success', 'Pengajuan berhasil disimpan.');
    }

    public function show($id)
    {
        // Mengambil data beserta relasi pendukung
        $data = PengajuanLabSubs::with(['karyawan', 'lab', 'subs', 'tracking', 'rkm.perusahaan', 'rkm.materi'])
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
        $jabatan = auth()->user()->jabatan;

        // === KHUSUS FINANCE ===
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

            $userObjs = User::whereHas('karyawan', function ($q) use ($data) {
                $q->where('kode_karyawan', $data->karyawan->kode_karyawan);
            })->get();

            $notifData = [
                'tanggal' => now(),
                'status' => $status,
            ];

            foreach ($userObjs as $user) {
                $receiverId = $user->id;
                NotificationFacade::send(
                    $user,
                    new ApprovalLabSubsNotification(
                        $notifData,
                        '/pengajuanlabsdansubs',
                        $data->karyawan->nama_lengkap,
                        'Update Status Pencairan oleh Finance',
                        $receiverId
                    )
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Status pencairan berhasil diperbarui!',
                'redirect' => route('pengajuanlabsdansubs.index')
            ]);
        }

        // === APPROVAL ===
        if ($request->approval == '1') {
            $status = '';
            $nextUser = null;

            // 1. Inisialisasi payload update
            $updatePayload = [];

            switch ($jabatan) {
                case 'SPV Sales':
                case 'Education Manager':
                case 'GM':
                    $status = "Telah disetujui oleh {$jabatan} dan sedang ditinjau oleh Koordinator ITSM";
                    $nextUser = Karyawan::where('jabatan', 'Koordinator ITSM')->first();
                    break;

                case 'Koordinator ITSM':
                    $status = "Telah disetujui oleh Koordinator ITSM dan sedang diproses oleh Finance";
                    $nextUser = Karyawan::where('jabatan', 'Finance & Accounting')->first();

                    // 2. LOGIC SNAPSHOT: Ambil data saat ini dari tabel master
                    if ($data->id_labs) {
                        $labData = \App\Models\Lab::find($data->id_labs);
                        if ($labData) {
                            $updatePayload['lab_snapshot'] = $labData->toArray();
                        }
                    } elseif ($data->id_subs) {
                        $subsData = \App\Models\Subscription::find($data->id_subs);
                        if ($subsData) {
                            $updatePayload['subs_snapshot'] = $subsData->toArray();
                        }
                    }
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Role Anda tidak memiliki hak approval!'
                    ], 403);
            }

            // Buat tracking history
            $e = TrackingPengajuanLabSubs::create([
                'id_pengajuan_lab_subs' => $id,
                'tracking' => $status,
                'tanggal'  => now()
            ]);

            // 3. PENTING: Masukkan id_tracking ke payload yang sama
            $updatePayload['id_tracking'] = $e->id;

            // 4. Eksekusi update (Snapshot + Tracking ID masuk bersamaan)
            $data->update($updatePayload);

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

        // === REJECT ===
        if ($request->approval == '2') {
            $alasan = $request->alasan ?? 'Tidak disebutkan';
            $status = "Pengajuan ditolak oleh {$jabatan} karena {$alasan}";

            $e = TrackingPengajuanLabSubs::create([
                'id_pengajuan_lab_subs' => $id,
                'tracking' => $status,
                'tanggal' => now()
            ]);

            $data->update(['id_tracking' => $e->id]);

            // 🔔 Kirim notifikasi ke pengaju
            $userObjs = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $data->karyawan->kode_karyawan))->get();

            $notifData = [
                'tanggal' => now(),
                'status' => $status,
            ];

            foreach ($userObjs as $user) {
                $receiverId = $user->id;
                NotificationFacade::send(
                    $user,
                    new ApprovalLabSubsNotification(
                        $notifData,
                        '/pengajuanlabsdansubs',
                        $data->karyawan->nama_lengkap,
                        'Menolak Pengajuan Lab/Subscription',
                        $receiverId
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
        $data = PengajuanLabSubs::with(['subs', 'lab'])->findOrFail($id);

        if ($data->subs) {
            $validated = $request->validate([
                'nama_subs'    => 'required|string|max:255',
                'merk'         => 'nullable|string|max:255',
                'desc'         => 'nullable|string',
                'subs_url'     => 'nullable|url',
                'access_code'  => 'nullable|string|max:255',
                'mata_uang'    => 'nullable|string|max:50',
                'kurs'         => 'nullable|numeric',
                'harga'        => 'nullable|numeric',
                'harga_rupiah' => 'nullable|string',
                'start_date'   => 'nullable|date',
                'end_date'     => 'nullable|date',
                'status'       => 'nullable|string|max:50',
            ]);

            // 💡 Konversi harga rupiah dari format tampilan ke angka
            if (!empty($validated['harga_rupiah'])) {
                $validated['harga_rupiah'] = (int) preg_replace('/[^\d]/', '', $validated['harga_rupiah']);
            }

            // 💡 Jika mata uang rupiah, pastikan kurs = 1 agar tidak null
            if ($validated['mata_uang'] === 'Rupiah') {
                $validated['kurs'] = 1;
            }

            $data->subs->update($validated);
        }

        elseif ($data->lab) {
            $validated = $request->validate([
                'nama_labs'        => 'required|string|max:255',
                'desc'             => 'nullable|string',
                'lab_url'          => 'nullable|url',
                'access_code'      => 'nullable|string|max:255',
                'duration_minutes' => 'nullable|numeric',
                'mata_uang'        => 'nullable|string|max:50',
                'kurs'             => 'nullable|numeric',
                'harga'            => 'nullable|numeric',
                'harga_rupiah'     => 'nullable|string',
                'start_date'       => 'nullable|date',
                'end_date'         => 'nullable|date',
                'status'           => 'nullable|string|max:50',
            ]);

            // 💡 Konversi harga rupiah ke angka
            if (!empty($validated['harga_rupiah'])) {
                $validated['harga_rupiah'] = (int) preg_replace('/[^\d]/', '', $validated['harga_rupiah']);
            }

            // 💡 Jika mata uang rupiah, kurs = 1
            if ($validated['mata_uang'] === 'Rupiah') {
                $validated['kurs'] = 1;
            }

            $data->lab->update($validated);
        }

        return redirect()
            ->route('pengajuanlabsdansubs.index')
            ->with('success', 'Data berhasil diperbarui!');
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
        $data = PengajuanLabSubs::with(['lab', 'subs', 'karyawan', 'tracking'])->findOrFail($id);

        // 🔹 Tentukan siapa yang "menyetujui" berdasarkan divisi
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

        return view('exports.pengajuan_labsubs-pdf', compact('data', 'finance', 'gm'));
    }

}
