<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSouvenir;
use App\Models\DetailPengajuanSouvenir;
use App\Models\TrackingPengajuanSouvenir;
use App\Models\karyawan;
use App\Models\User;
use App\Models\vendorSouvenir;
use App\Models\souvenir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\PengajuanSouvenirNotification;
use App\Notifications\ApprovalSouvenirNotification;
use Illuminate\Support\Facades\Storage;

class PengajuanSouvenirController extends Controller
{
    /**
     * Menampilkan daftar Pengajuan Souvenir.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->karyawan || !$user->karyawan->jabatan) {
            return redirect()->route('login');
        }

        $jabatan = $user->karyawan->jabatan;
        $tracking = 'buka';

        // Pengecekan status buka/tutup pengajuan (Hanya berlaku untuk CC/Admin Holding)
        if ($jabatan === 'Customer Care' || $jabatan === 'Admin Holding') {
            $trackingRecord = TrackingPengajuanSouvenir::whereHas('pengajuanSouvenir.karyawan', function ($query) use ($user) {
                $query->where('id', $user->karyawan_id);
            })
            ->latest()
            ->first();

            // $tracking = $this->determineTrackingStatus($trackingRecord);
        }

        return view('pengajuansouvenir.index', compact('tracking'));
    }

    /**
     * Fungsi untuk menentukan status tracking (Buka/Tutup)
     */
    private function determineTrackingStatus($trackingRecord)
    {
        if (is_null($trackingRecord)) {
            return 'buka';
        }
        if (
            $trackingRecord->tracking === 'Selesai' ||
            str_starts_with($trackingRecord->tracking, 'Ditolak')
        ) {
            return 'buka';
        }
        return 'tutup';
    }

    /**
     * API untuk mengambil data pengajuan (untuk DataTables, dll)
     */
    public function getPengajuanSouvenir($month, $year)
    {
        $userKaryawan = auth()->user()->karyawan;
        if (!$userKaryawan) {
            return response()->json(['data' => []], 401);
        }

        $jabatan = $userKaryawan->jabatan;

        $query = PengajuanSouvenir::with([
                        'karyawan',
                        'tracking',
                        'vendor',
                        'detail.souvenir'
                    ])
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year);

        // PERBAIKAN: GM, Finance, Customer Care, DAN Admin Holding melihat SEMUA data pengajuan.
        if ($jabatan === 'GM' || $jabatan === 'Finance & Accounting' || $jabatan === 'Customer Care' || $jabatan === 'Admin Holding') {
            // Melihat semua, tidak perlu batasan WHERE
        } else {
            // Peran lain tidak berhak mengakses
            return response()->json(['data' => []]);
        }

        $pengajuanSouvenir = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'List Pengajuan Souvenir',
            'data' => $pengajuanSouvenir,
        ]);
    }

    /**
     * Menampilkan form untuk membuat Pengajuan Souvenir baru.
     */
    public function create()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);

        // Akses create: Customer Care ATAU Admin Holding
        if ($karyawan->jabatan !== 'Customer Care' && $karyawan->jabatan !== 'Admin Holding') {
             return redirect()->route('pengajuansouvenir.index')->with('error', 'Hanya Customer Care atau Admin Holding yang dapat mengakses halaman ini.');
        }

        $vendors = vendorSouvenir::where('is_active', 1)->get();
        $souvenirs = Souvenir::all();

        return view('pengajuansouvenir.create', compact('karyawan', 'vendors', 'souvenirs'));
    }

    /**
     * Menyimpan Pengajuan Souvenir baru ke dalam database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|exists:karyawans,id',
            'id_vendor' => 'required|exists:vendor_souvenirs,id',
            'souvenir.id.*' => 'required|exists:souvenirs,id',
            'souvenir.pax.*' => 'required|numeric|min:1',
            'souvenir.harga_satuan.*' => 'required|string',
        ]);

        $karyawan = karyawan::findOrFail($request->id_karyawan);
        // Otorisasi store: Customer Care ATAU Admin Holding
        if ($karyawan->jabatan !== 'Customer Care' && $karyawan->jabatan !== 'Admin Holding') {
            return redirect()->back()->with('error', 'Hanya Customer Care atau Admin Holding yang dapat membuat pengajuan.');
        }

        $souvenirIds = $request->input('souvenir.id');
        $paxes = $request->input('souvenir.pax');
        $hargaSatuans = $request->input('souvenir.harga_satuan');

        $detailData = [];
        $totalKeseluruhan = 0;

        for ($i = 0; $i < count($souvenirIds); $i++) {
            $hargaNumeric = (int)str_replace('.', '', $hargaSatuans[$i]);
            $paxNumeric = (int)$paxes[$i];
            $totalItem = $hargaNumeric * $paxNumeric;

            $detailData[] = [
                'id_souvenir' => $souvenirIds[$i],
                'pax' => $paxNumeric,
                'harga_satuan' => $hargaNumeric,
                'harga_total' => $totalItem,
            ];
            $totalKeseluruhan += $totalItem;
        }

        try {
            DB::beginTransaction();

            $pengajuan = PengajuanSouvenir::create([
                'id_karyawan' => $request->id_karyawan,
                'id_vendor' => $request->id_vendor,
                'total_keseluruhan' => $totalKeseluruhan,
            ]);

            foreach ($detailData as &$item) {
                $item['id_pengajuan_souvenir'] = $pengajuan->id;
            }

            DetailPengajuanSouvenir::insert($detailData);

            $statusAwal = 'Diajukan dan Sedang Ditinjau oleh General Manager';

            $tracking = TrackingPengajuanSouvenir::create([
                'id_pengajuan_souvenir' => $pengajuan->id,
                'tracking' => $statusAwal,
                'tanggal' => now(),
            ]);

            $pengajuan->update([
                'id_tracking' => $tracking->id,
            ]);

            $gm = karyawan::where('jabatan', 'GM')->first();
            if ($gm) {
                $userGM = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $gm->kode_karyawan))->first();
                if ($userGM) {
                    $dataNotif = [
                        'id_karyawan' => $request->id_karyawan,
                        'tipe' => 'Souvenir',
                        'tanggal_pengajuan' => now()
                    ];
                    $type = 'Mengajukan Permintaan Souvenir';
                    $path = '/pengajuansouvenir';
                    NotificationFacade::send($userGM, new PengajuanSouvenirNotification($dataNotif, $path, $type));
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan pengajuan: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('pengajuansouvenir.index')->with('success', 'Pengajuan Souvenir berhasil dibuat.');
    }

    /**
     * Menampilkan detail Pengajuan Souvenir tertentu.
     */
    public function show($id)
    {
        $data = PengajuanSouvenir::with([
                    'karyawan',
                    'tracking',
                    'vendor',
                    'detail.souvenir'
                ])->findOrFail($id);

        $tracking = TrackingPengajuanSouvenir::where('id_pengajuan_souvenir', $id)
                        ->orderBy('created_at', 'asc')->get();

        $souvenirs = Souvenir::all(); // Diperlukan untuk modal edit

        return view('pengajuansouvenir.show', compact('data', 'tracking', 'souvenirs'));
    }

    /**
     * Menampilkan form untuk mengedit Pengajuan Souvenir.
     */
    public function edit($id)
    {
        $pengajuan = PengajuanSouvenir::with('detail')->findOrFail($id);
        $karyawan = karyawan::findOrFail($pengajuan->id_karyawan);
        $statusTerakhir = $pengajuan->tracking->tracking;

        // Otorisasi edit: Pemilik adalah Customer Care ATAU Admin Holding
        $isOwnerOrAdmin = ($karyawan->jabatan === 'Customer Care' || $karyawan->jabatan === 'Admin Holding') && (auth()->user()->karyawan_id === $pengajuan->id_karyawan);

        if (!$isOwnerOrAdmin) {
            return redirect()->route('pengajuansouvenir.index')->with('error', 'Anda tidak berhak mengedit pengajuan ini.');
        }

        // Logika batasan status edit tetap berlaku
        if (!str_starts_with($statusTerakhir, 'Ditolak') && $statusTerakhir !== 'Diajukan dan Sedang Ditinjau oleh General Manager') {
           return redirect()->route('pengajuansouvenir.show', $id)->with('error', 'Data tidak dapat diedit karena sedang diproses.');
        }

        $vendors = vendorSouvenir::where('is_active', 1)->get();
        $souvenirs = souvenir::all();

        return view('pengajuansouvenir.edit', compact('pengajuan', 'karyawan', 'vendors', 'souvenirs'));
    }


    /**
     * Memperbarui Pengajuan Souvenir (Approval / Penolakan).
     */
    public function update(Request $request, $id)
    {
        $pengajuan = PengajuanSouvenir::with('karyawan', 'tracking')->findOrFail($id);
        $jabatan = auth()->user()->karyawan->jabatan;
        $status = '';
        $notifType = '';

        $karyawanCC = $pengajuan->karyawan;
        $userCC = User::where('karyawan_id', $karyawanCC->id)->first();
        $gm = karyawan::where('jabatan', 'GM')->first();
        $userGM = $gm ? User::where('karyawan_id', $gm->id)->first() : null;
        $finance = karyawan::where('jabatan', 'Finance & Accounting')->first();
        $userFinance = $finance ? User::where('karyawan_id', $finance->id)->first() : null;

        $usersToNotify = [];
        if ($userCC) $usersToNotify[] = $userCC;

        if ($pengajuan->tracking->tracking === 'Selesai') {
             return redirect()->route('pengajuansouvenir.index')->with('error', 'Pengajuan ini sudah Selesai dan tidak dapat diubah lagi.');
        }

        $approval = $request->input('approval');
        $alasan = $request->input('alasan', '');

        if ($approval == '2') { // PROSES TOLAK
            $notifType = 'Pengajuan Souvenir Ditolak';
            if ($jabatan === 'GM') {
                $status = 'Ditolak GM: ' . $alasan;
            } elseif ($jabatan === 'Finance & Accounting') {
                $status = 'Pencairan Ditolak Finance: ' . $alasan;
                if ($userGM) $usersToNotify[] = $userGM;
            } else {
                return redirect()->route('pengajuansouvenir.index')->with('error', 'Anda tidak memiliki wewenang untuk menolak.');
            }

        } elseif ($approval == '1') { // PROSES SETUJU / UPDATE STATUS
            $notifType = 'Pengajuan Souvenir Diperbarui';

            if ($jabatan === 'GM') {
                if ($pengajuan->tracking->tracking === 'Diajukan dan Sedang Ditinjau oleh General Manager') {
                    $status = 'Telah disetujui oleh General Manager dan sedang diproses Finance';
                    if ($userFinance) $usersToNotify[] = $userFinance;
                } else {
                    return redirect()->route('pengajuansouvenir.show', $id)->with('error', 'Status pengajuan sudah berubah.');
                }
            }
            elseif ($jabatan === 'Finance & Accounting') {
                if (str_starts_with($pengajuan->tracking->tracking, 'Ditolak')) {
                     return redirect()->route('pengajuansouvenir.index')->with('error', 'Pengajuan ini sudah Ditolak dan tidak dapat diubah lagi.');
                }

                $status = $request->input('status');

                if (empty($status)) {
                    $status = 'Pencairan Sudah Selesai';
                }

                if ($userGM) $usersToNotify[] = $userGM;
            }
            else {
                return redirect()->route('pengajuansouvenir.index')->with('error', 'Anda tidak memiliki wewenang untuk menyetujui.');
            }
        } else {
             return redirect()->route('pengajuansouvenir.show', $id)->with('info', 'Tidak ada tindakan approval dipilih.');
        }

        if ($status && $notifType) {
            $tracking = TrackingPengajuanSouvenir::create([
                'id_pengajuan_souvenir' => $id,
                'tracking' => $status,
                'tanggal' => now()
            ]);
            $pengajuan->update(['id_tracking' => $tracking->id]);

            $notifData = ['tanggal' => now(), 'status' => $status];
            $path = '/pengajuansouvenir';
            $to = $karyawanCC->nama_lengkap;

            $uniqueUsers = array_filter(array_unique($usersToNotify));
            foreach ($uniqueUsers as $user) {
                if($user) {
                    $receiverId = $user->id;
                    NotificationFacade::send($user, new ApprovalSouvenirNotification($notifData, $path, $to, $notifType, $receiverId));
                }
            }
        }
        return redirect()->route('pengajuansouvenir.index')->with('success', 'Status Pengajuan Souvenir berhasil diperbarui.');
    }


    public function updateItems(Request $request, $id)
    {
        $pengajuan = PengajuanSouvenir::with('detail', 'karyawan', 'tracking')->findOrFail($id);
        $userKaryawanId = auth()->user()->karyawan_id;
        $userJabatan = auth()->user()->karyawan->jabatan;
        $statusTerakhir = $pengajuan->tracking->tracking;

        $isOwner = ($userKaryawanId === $pengajuan->id_karyawan);
        $isFinance = ($userJabatan === 'Finance & Accounting');

        // Otorisasi updateItems: Pemilik adalah Customer Care ATAU Admin Holding, ATAU Finance
        $isAdminHoldingOrCC = ($userJabatan === 'Admin Holding' || $userJabatan === 'Customer Care');
        $isAuthorized = ($isOwner && $isAdminHoldingOrCC) || $isFinance;


        // if (!$isAuthorized) {
        //     return redirect()->back()->with('error', 'Anda tidak berhak mengubah data item ini.');
        // }
        if ($statusTerakhir === 'Selesai' || str_contains($statusTerakhir, 'Ditolak')) {
             return redirect()->back()->with('error', 'Data tidak dapat diubah karena pengajuan sudah Selesai/Ditolak.');
        }

        $request->validate([
            'souvenir.detail_id.*' => 'nullable|integer',
            'souvenir.id.*' => 'required|exists:souvenirs,id',
            'souvenir.pax.*' => 'required|numeric|min:1',
            'souvenir.harga_satuan.*' => 'required|string',
            'deleted_items.*' => 'nullable|integer'
        ]);

        try {
            DB::beginTransaction();

            $submittedDetailIds = [];
            $totalKeseluruhan = 0;

            // 1. Loop Update/Create
            foreach ($request->souvenir['id'] as $index => $souvenirId) {
                $detailId = $request->souvenir['detail_id'][$index] ?? null;

                $hargaNumeric = (int)str_replace('.', '', $request->souvenir['harga_satuan'][$index]);
                $paxNumeric = (int)$request->souvenir['pax'][$index];
                $totalItem = $hargaNumeric * $paxNumeric;

                $detailData = [
                    'id_pengajuan_souvenir' => $pengajuan->id,
                    'id_souvenir' => $souvenirId,
                    'pax' => $paxNumeric,
                    'harga_satuan' => $hargaNumeric,
                    'harga_total' => $totalItem,
                ];

                $detail = DetailPengajuanSouvenir::updateOrCreate(
                    ['id' => $detailId],
                    $detailData
                );

                $submittedDetailIds[] = $detail->id;
                $totalKeseluruhan += $totalItem;
            }

            // 2. Hapus Item
            if ($request->has('deleted_items')) {
                DetailPengajuanSouvenir::destroy($request->deleted_items);
            }
            $existingIds = $pengajuan->detail->pluck('id')->all();
            $itemsToDelete = array_diff($existingIds, $submittedDetailIds);
            DetailPengajuanSouvenir::destroy($itemsToDelete);


            // 3. Update Total
            $pengajuan->update([
                'total_keseluruhan' => $totalKeseluruhan
            ]);

            // 4. Tracking
            $trackingMessage = $isFinance
                                ? 'Detail item diubah oleh Finance (Perlu ditinjau ulang)'
                                : 'Detail item diubah oleh Pengaju (Perlu ditinjau ulang)';

            TrackingPengajuanSouvenir::create([
                'id_pengajuan_souvenir' => $pengajuan->id,
                'tracking' => $trackingMessage,
                'tanggal' => now()
            ]);

            // 5. Kirim Notifikasi (Owner notif GM, Finance notif Owner)
            if ($isOwner || $isFinance) {
                $gm = karyawan::where('jabatan', 'GM')->first();
                $usersToNotify = [];

                if ($isOwner && $gm) {
                     $usersToNotify[] = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $gm->kode_karyawan))->first();
                }
                if ($isFinance) {
                    $usersToNotify[] = User::where('karyawan_id', $pengajuan->id_karyawan)->first();
                }

                foreach (array_filter(array_unique($usersToNotify)) as $user) {
                    $notifType = 'Pengajuan Souvenir Diperbarui';
                    $notifData = ['tanggal' => now(), 'status' => $trackingMessage];
                    $path = '/pengajuansouvenir';
                    $to = $pengajuan->karyawan->nama_lengkap;

                    if ($user) {
                        NotificationFacade::send($user, new ApprovalSouvenirNotification($notifData, $path, $to, $notifType));
                    }
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui item: ' . $e->getMessage());
        }

        return redirect()->route('pengajuansouvenir.show', $id)->with('success', 'Detail item berhasil diperbarui.');
    }

    public function updateInvoice(Request $request, $id)
    {
        $pengajuan = PengajuanSouvenir::with('tracking')->findOrFail($id);

        // Validasi file
        $request->validate([
            'invoice' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('invoice')) {
            // 1. Hapus file lama jika ada (untuk menghindari sampah file)
            if ($pengajuan->invoice) {
                Storage::delete('public/' . $pengajuan->invoice);
            }

            // 2. Simpan file baru
            $file = $request->file('invoice');
            // Nama file unik: invoice_ID_TIMESTAMP.ext
            $filename = 'invoice_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengajuan_souvenir', $filename, 'public');

            if ($pengajuan->tracking->tracking === 'Pencairan Sudah Selesai') {

                $statusBaru = 'Selesai';

                // Buat tracking baru 'Selesai'
                $newTracking = TrackingPengajuanSouvenir::create([
                    'id_pengajuan_souvenir' => $id,
                    'tracking' => $statusBaru,
                    'tanggal' => now()
                ]);

                // Update pengajuan dengan invoice dan id_tracking baru
                $pengajuan->update([
                    'invoice' => $path,
                    'id_tracking' => $newTracking->id
                ]);

            } else {
                // Jika belum tahap pencairan selesai, hanya update kolom invoice saja
                $pengajuan->update([
                    'invoice' => $path,
                ]);
            }

            return redirect()->back()->with('success', 'Invoice berhasil diunggah.');
        }

        return redirect()->back()->with('error', 'Gagal mengunggah invoice.');
    }

    /**
     * Menghapus Pengajuan Souvenir dari database.
     */
    public function destroy($id)
    {
        $data = PengajuanSouvenir::findOrFail($id);

        if (auth()->user()->karyawan_id !== $data->id_karyawan && auth()->user()->karyawan->jabatan !== 'GM') {
             return redirect()->route('pengajuansouvenir.index')->with('error', 'Anda tidak berhak menghapus data ini.');
        }

        // DIPERBARUI: Hapus semua data terkait
        try {
            DB::beginTransaction();
            // 1. Hapus semua detail (Anak)
            DetailPengajuanSouvenir::where('id_pengajuan_souvenir', $id)->delete();
            // 2. Hapus semua tracking
            TrackingPengajuanSouvenir::where('id_pengajuan_souvenir', $id)->delete();
            // 3. Hapus data utama (Induk)
            $data->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pengajuansouvenir.index')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }

        return redirect()->route('pengajuansouvenir.index')->with('success', 'Pengajuan Souvenir berhasil dihapus!');
    }

    public function exportPDF($id)
    {
        $data = PengajuanSouvenir::with(['detail.souvenir', 'tracking', 'karyawan', 'vendor'])->findOrFail($id);

        $gm = karyawan::where('jabatan', 'GM')->latest()->first();

        $finance = karyawan::where('jabatan', 'Finance & Accounting')->latest()->first();

        $penyetuju = $gm;
        $pelaksana = $finance;

        return view('exports.pengajuan_souvenir-pdf', compact('data', 'penyetuju', 'pelaksana'));
    }
}
