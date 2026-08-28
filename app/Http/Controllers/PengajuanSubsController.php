<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\karyawan;
use App\Models\jabatan;
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
    private function checkKoordinatorItsmAccess()
    {
        $user = auth()->user();
        if (!$user || !$user->karyawan) {
            return false;
        }

        $jabatan = $user->karyawan->jabatan ?? '';
        return $jabatan === 'Koordinator ITSM';
    }

    public function index()
    {
        $user = auth()->user();

        if (!$user || !$user->karyawan) {
            return view('auth.login');
        }

        if (!$this->checkKoordinatorItsmAccess()) {
            return redirect()->route('home')->with('error', 'Hanya Koordinator ITSM yang dapat mengakses fitur Pengajuan Subs.');
        }

        $tracking = 'buka';
        $materis = Materi::all();

        return view('pengajuansubs.index', compact('tracking', 'materis'));
    }

    public function getPengajuanLabSubs($month, $year)
    {
        if (!$this->checkKoordinatorItsmAccess()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $relations = ['karyawan', 'tracking', 'subs', 'rkm.perusahaan', 'rkm.materi'];

        $Pengajuan = PengajuanSubs::with($relations)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'List Pengajuan Subs',
            'data'    => $Pengajuan,
        ]);
    }

    public function getMasterLabs()
    {
        if (!$this->checkKoordinatorItsmAccess()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $subs = Subscription::with('materis')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $subs
        ]);
    }

    public function storeMasterSubs(Request $request)
    {
        if (!$this->checkKoordinatorItsmAccess()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $request->validate([
            'nama_subs' => 'required|string|max:255',
            'merk' => 'nullable|string|max:255',
            'tipe' => 'required|in:subscription,one-time',
            'status' => 'required|in:active,pending,expired',
            'desc' => 'nullable|string',
            'subs_url' => 'nullable|url',
            'access_code' => 'nullable|string|max:255',
            'duration_minutes' => 'nullable|numeric',
            'mata_uang' => 'required|string|max:50',
            'harga' => 'required|numeric',
            'kurs' => 'nullable|numeric',
            'harga_rupiah' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'materi_ids' => 'nullable|array',
            'materi_ids.*' => 'exists:materis,id',
        ]);

        $user = auth()->user();

        $data = $request->except('materi_ids');
        $data['kode_karyawan'] = $user->karyawan->kode_karyawan ?? null;

        if (empty($data['harga_rupiah'])) {
            $kurs = $data['kurs'] ?? 1;
            $data['harga_rupiah'] = $data['harga'] * $kurs;
        }

        $subs = Subscription::create($data);

        if ($request->has('materi_ids')) {
            $subs->materis()->sync($request->materi_ids);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Subscription berhasil ditambahkan!',
            'data' => $subs
        ]);
    }

    public function updateMasterLab(Request $request, $id)
    {
        if (!$this->checkKoordinatorItsmAccess()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $request->validate([
            'nama_labs' => 'required|string|max:255',
            'merk' => 'nullable|string|max:255',
            'tipe' => 'nullable|in:subscription,one-time',
            'status' => 'nullable|in:active,pending,expired',
            'harga_rupiah' => 'nullable|numeric',
            'materi_ids' => 'nullable|array',
            'materi_ids.*' => 'exists:materis,id'
        ]);

        $subs = Subscription::findOrFail($id);

        $updateData = $request->except('materi_ids');
        if (isset($updateData['nama_labs'])) {
            $updateData['nama_subs'] = $updateData['nama_labs'];
            unset($updateData['nama_labs']);
        }
        if (isset($updateData['lab_url'])) {
            $updateData['subs_url'] = $updateData['lab_url'];
            unset($updateData['lab_url']);
        }

        $subs->update($updateData);

        if ($request->has('materi_ids')) {
            $subs->materis()->sync($request->materi_ids);
        } else {
            $subs->materis()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Data subscription berhasil diperbarui',
            'data' => $subs
        ]);
    }

    public function edit($id)
    {
        if (!$this->checkKoordinatorItsmAccess()) {
            return redirect()->route('pengajuansubs.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit data teknis.');
        }

        $data = PengajuanSubs::with(['subs'])->findOrFail($id);

        return view('pengajuansubs.edit', compact('data'));
    }

    public function create()
    {
        if (!$this->checkKoordinatorItsmAccess()) {
            return redirect()->route('pengajuansubs.index')
                ->with('error', 'Hanya Koordinator ITSM yang dapat membuat pengajuan.');
        }

        $user = auth()->user();
        $karyawan = $user->karyawan;

        $rkms = RKM::with(['materi', 'perusahaan'])
            ->whereDate('tanggal_awal', '>=', now())
            ->orderBy('tanggal_awal', 'asc')
            ->get();

        return view('pengajuansubs.create', compact('karyawan', 'rkms'));
    }

    public function getLabsByRkm($rkmId)
    {
        $rkm = RKM::with('materi')->find($rkmId);

        if (!$rkm || !$rkm->materi) {
            return response()->json(['materi_nama' => '', 'labs' => []]);
        }

        $subs = $rkm->materi->subscriptions()
                    ->where('subscriptions.status', 'active')
                    ->where('subscriptions.is_active', true)
                    ->get();

        return response()->json([
            'materi_nama' => $rkm->materi->nama_materi,
            'labs' => $subs
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->checkKoordinatorItsmAccess()) {
            return redirect()->route('pengajuansubs.index')->with('error', 'Akses ditolak');
        }

        $request->validate([
            'id_rkm' => 'required|exists:r_k_m_s,id',
            'sumber_lab' => 'required|in:existing,new',
        ]);

        $karyawan = \App\Models\karyawan::where('kode_karyawan', $request->kode_karyawan)->firstOrFail();

        $subsId = null;
        $jenisTransaksi = 'existing';
        $namaSubsNotification = '';
        $descSubsNotification = '';

        $trackingText = 'Diajukan dan Sedang Ditinjau oleh Koordinator ITSM';

        if ($request->sumber_lab === 'existing') {
            $request->validate(['id_existing_lab' => 'required|exists:subscriptions,id']);

            $subsId = $request->id_existing_lab;
            $jenisTransaksi = 'existing';

            $existingSubs = Subscription::find($subsId);
            $namaSubsNotification = $existingSubs->nama_subs ?? '';
            $descSubsNotification = $existingSubs->desc ?? '';
        } else {
            $request->validate([
                'new_nama_labs' => 'required|string|max:255',
                'new_merk'      => 'required|string|max:255',
            ]);

            $newSubs = Subscription::create([
                'kode_karyawan' => $karyawan->kode_karyawan,
                'nama_subs'     => $request->new_nama_labs,
                'merk'          => $request->new_merk,
                'tipe'          => 'subscription',
                'desc'          => 'Request Baru oleh Koordinator ITSM',
                'subs_url'      => null,
                'status'        => 'pending',
                'is_active'     => false,
            ]);

            $subsId = $newSubs->id;
            $jenisTransaksi = 'baru';

            $namaSubsNotification = $request->new_nama_labs;
            $descSubsNotification = 'Request Subs Baru';
        }

        $pengajuan = PengajuanSubs::create([
            'kode_karyawan'   => $karyawan->kode_karyawan,
            'id_subs'         => $subsId,
            'id_rkm'          => $request->id_rkm,
            'jenis_transaksi' => $jenisTransaksi,
        ]);

        $trackingModel = TrackingPengajuanSubs::create([
            'id_pengajuan_subs' => $pengajuan->id,
            'tracking'          => $trackingText,
            'tanggal'           => now(),
        ]);

        $pengajuan->update(['id_tracking' => $trackingModel->id]);

        return redirect()->route('pengajuansubs.index')
            ->with('success', 'Pengajuan Subs berhasil dikirim.');
    }

    public function show($id)
    {
        if (!$this->checkKoordinatorItsmAccess()) {
            return redirect()->route('home')->with('error', 'Akses ditolak');
        }

        $data = PengajuanSubs::with(['karyawan', 'subs','tracking', 'rkm.perusahaan', 'rkm.materi'])
            ->findOrFail($id);

        return view('pengajuansubs.show', compact('data'));
    }

    public function update(Request $request, $id)
    {
        if (!$this->checkKoordinatorItsmAccess()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $request->validate([
            'approval' => 'required|string',
            'alasan' => 'nullable|string|max:500',
        ]);

        $data = PengajuanSubs::with('karyawan')->findOrFail($id);

        if ($request->approval == '1') {
            $status = "Telah disetujui oleh Koordinator ITSM dan lihat akses nya di Detail";

            if ($data->id_subs) {
                $subsData = Subscription::find($data->id_subs);
                if ($subsData) {
                    $data->update(['subs_snapshot' => $subsData->toArray()]);
                }
            }

            $e = TrackingPengajuanSubs::create([
                'id_pengajuan_subs' => $id,
                'tracking'          => $status,
                'tanggal'           => now()
            ]);

            $final = TrackingPengajuanSubs::create([
                'id_pengajuan_subs' => $id,
                'tracking'          => 'Selesai',
                'tanggal'           => now()->addSeconds(1)
            ]);

            $data->update(['id_tracking' => $final->id]);

            return response()->json([
                'success'  => true,
                'message'  => 'Approval berhasil disimpan!',
                'redirect' => route('pengajuansubs.index')
            ]);
        }

        if ($request->approval == '2') {
            $alasan = $request->alasan ?? 'Tidak disebutkan';
            $status = "Pengajuan ditolak oleh Koordinator ITSM karena {$alasan}";

            $e = TrackingPengajuanSubs::create([
                'id_pengajuan_subs' => $id,
                'tracking'          => $status,
                'tanggal'           => now()
            ]);

            $data->update(['id_tracking' => $e->id]);

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil ditolak!',
                'redirect' => route('pengajuansubs.index')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada aksi yang diproses!'
        ], 400);
    }

    public function updateLabSubs(Request $request, $id)
    {
        if (!$this->checkKoordinatorItsmAccess()) {
            return redirect()->route('pengajuansubs.index')->with('error', 'Akses ditolak');
        }

        $data = PengajuanSubs::with('subs')->findOrFail($id);

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

        $updateData = [
            'nama_subs'        => $validated['nama_labs'],
            'merk'             => $validated['merk'],
            'tipe'             => $validated['tipe'],
            'desc'             => $validated['desc'],
            'subs_url'         => $validated['lab_url'],
            'access_code'      => $validated['access_code'],
            'duration_minutes' => $validated['duration_minutes'],
            'mata_uang'        => $validated['mata_uang'],
            'harga'            => $validated['harga'],
            'kurs'             => $validated['kurs'] ?? 1,
            'start_date'       => $validated['start_date'],
            'end_date'         => $validated['end_date'],
            'status'           => $validated['status'],
        ];

        if (!empty($validated['harga_rupiah'])) {
            $updateData['harga_rupiah'] = (int) preg_replace('/[^\d]/', '', $validated['harga_rupiah']);
        } else {
            $updateData['harga_rupiah'] = $updateData['harga'] * $updateData['kurs'];
        }

        if ($data->subs) {
            $data->subs->update($updateData);
        }

        return redirect()
            ->route('pengajuansubs.index')
            ->with('success', 'Data Teknis Subs berhasil diperbarui!');
    }

    public function uploadInvoice(Request $request, $id)
    {
        if (!$this->checkKoordinatorItsmAccess()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $request->validate([
            'invoice' => 'required|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = PengajuanSubs::findOrFail($id);

        if ($data->invoice && Storage::exists('public/pengajuanlabsubs/' . $data->invoice)) {
            Storage::delete('public/pengajuanlabsubs/' . $data->invoice);
        }

        $filename = 'invoice_subs_' . $id . '_' . time() . '.' . $request->file('invoice')->getClientOriginalExtension();
        $request->file('invoice')->storeAs('public/pengajuanlabsubs', $filename);

        $data->update(['invoice' => $filename]);

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil diunggah!',
            'file' => asset('storage/pengajuanlabsubs/' . $filename),
        ]);
    }

    public function exportPDF($id)
    {
        if (!$this->checkKoordinatorItsmAccess()) {
            return redirect()->route('home')->with('error', 'Akses ditolak');
        }

        $data = PengajuanSubs::with(['subs', 'karyawan', 'tracking'])->findOrFail($id);

        $subsSnapshot = null;
        if (!empty($data->subs_snapshot)) {
            $subsSnapshot = is_string($data->subs_snapshot) ? json_decode($data->subs_snapshot) : (object) $data->subs_snapshot;
        } elseif ($data->subs) {
            $subsSnapshot = (object) $data->subs->toArray();
        }

        $labSnapshot = null;
        $finance = Karyawan::where('jabatan', 'Koordinator ITSM')->latest()->first();
        $gm = Karyawan::where('jabatan', 'GM')->latest()->first();

        return view('exports.pengajuan_labsubs-pdf', compact('data', 'finance', 'gm', 'labSnapshot', 'subsSnapshot'));
    }

    public function renewLab($id)
    {
        if (!$this->checkKoordinatorItsmAccess()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $user = auth()->user();
        $karyawan = $user->karyawan;

        $subs = Subscription::findOrFail($id);

        $pengajuan = PengajuanSubs::create([
            'kode_karyawan'   => $karyawan->kode_karyawan,
            'id_subs'         => $subs->id,
            'id_rkm'          => null,
            'jenis_transaksi' => 'pembaharuan',
        ]);

        $trackingText = 'Pengajuan Pembaharuan Subs Diajukan dan Sedang Ditinjau oleh Koordinator ITSM';

        $trackingModel = TrackingPengajuanSubs::create([
            'id_pengajuan_subs' => $pengajuan->id,
            'tracking'          => $trackingText,
            'tanggal'           => now(),
        ]);

        $pengajuan->update(['id_tracking' => $trackingModel->id]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan pembaharuan untuk subs ' . $subs->nama_subs . ' berhasil dibuat!'
        ]);
    }
}
