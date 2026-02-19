<?php

namespace App\Http\Controllers;

use App\Models\Sertifikasi;
use App\Models\Pelatihan;
use App\Models\User;
use App\Models\karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\DevelopmentNotification;
use Carbon\Carbon;
use App\Models\tracking_pengajuan_barang;
use App\Models\PengajuanBarang;
use App\Models\detailPengajuanBarang;
use App\Notifications\ApprovalbarangNotification;
use Illuminate\Support\Facades\Storage;
use App\Models\SpecializationArea;


class InstructorDevelopmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->karyawan || !$user->karyawan->jabatan) {
            return redirect()->route('login');
        }

        $jabatan = $user->karyawan->jabatan;
        $isManager = ($jabatan === 'Education Manager');

        // 1. Query Sertifikasi
        $sertifikasiQuery = Sertifikasi::with([
            'user.karyawan',
            'approver',
            'pelatihan.pengajuan_barang.tracking',
            'pengajuan_barang.tracking'
        ]);

        // 2. Query Pelatihan
        $pelatihanQuery = Pelatihan::with(['user.karyawan', 'approver', 'pengajuan_barang.tracking']);

        if (!$isManager) {
            $sertifikasiQuery->where('user_id', $user->id);
            $pelatihanQuery->where('user_id', $user->id);
        }

        $sertifikasis = $sertifikasiQuery->latest()->get();
        $pelatihans = $pelatihanQuery->latest()->get();

        // 3. Query Specialization Area
        if ($isManager) {
            // Manager: Melihat SEMUA data specialization
            $specializations = SpecializationArea::latest()->get();
        } else {
            // Non-Manager: Melihat HANYA data miliknya sendiri
            $specializations = collect();
            if ($user->karyawan && $user->karyawan->kode_karyawan) {
                $specializations = SpecializationArea::where('kode_instruktur', $user->karyawan->kode_karyawan)
                    ->latest()
                    ->get();
            }
        }

        return view('development.index', compact('sertifikasis', 'pelatihans', 'specializations'));
    }

    public function storeSertifikasi(Request $request)
    {
        $request->validate([
            'nama_sertifikat'        => 'required|string|max:255',
            'tanggal_ujian'          => 'required|date',
            'tanggal_berlaku_dari'   => 'nullable|date',
            'tanggal_berlaku_sampai' => 'nullable|date|after_or_equal:tanggal_berlaku_dari',
            'harga'                  => 'nullable|numeric|min:0', // Tidak required
            'vendor'                 => 'required|string|max:255',
            'keterangan'             => 'nullable|string',
        ]);

        $userId = Auth::id();

        $existingCert = Sertifikasi::where('user_id', $userId)
            ->where('nama_sertifikat', $request->nama_sertifikat)
            ->where('vendor', $request->vendor)
            ->orderByDesc('tanggal_berlaku_sampai')
            ->first();

        if ($existingCert) {
            if (is_null($existingCert->tanggal_berlaku_sampai)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Gagal: Sertifikasi ini sudah terdaftar dan statusnya berlaku seumur hidup.');
            }

            if ($request->tanggal_ujian <= $existingCert->tanggal_berlaku_sampai) {
                $formattedDate = Carbon::parse($existingCert->tanggal_berlaku_sampai)->translatedFormat('d F Y');
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Gagal: Sertifikasi serupa masih berlaku hingga {$formattedDate}.");
            }
        }

        try {
            DB::beginTransaction();

            $harga = $request->harga ?? 0;

            $statusApproval = ($harga > 0) ? 'pending' : 'approved';

            $sertifikasi = Sertifikasi::create([
                'user_id'                => Auth::id(),
                'nama_sertifikat'        => $request->nama_sertifikat,
                'tanggal_ujian'          => $request->tanggal_ujian,
                'tanggal_berlaku_dari'   => $request->tanggal_berlaku_dari,
                'tanggal_berlaku_sampai' => $request->tanggal_berlaku_sampai,
                'harga'                  => $harga,
                'vendor'                 => $request->vendor,
                'keterangan'             => $request->keterangan,
                'status_approval'        => $statusApproval,
            ]);

            if ($statusApproval === 'pending') {
                $managers = karyawan::where('jabatan', 'Education Manager')->get();
                foreach ($managers as $manager) {
                    $userManager = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $manager->kode_karyawan))->first();

                    if ($userManager) {
                        $dataNotif = [
                            'id_user'           => Auth::id(),
                            'tipe_kategori'     => 'Sertifikasi',
                            'nama_item'         => $sertifikasi->nama_sertifikat,
                            'tanggal_pengajuan' => now(),
                            'tanggal_ujian'     => $sertifikasi->tanggal_ujian,
                            'berlaku_dari'      => $sertifikasi->tanggal_berlaku_dari,
                            'berlaku_sampai'    => $sertifikasi->tanggal_berlaku_sampai,
                            'harga'             => $sertifikasi->harga,
                        ];

                        $type = 'Mengajukan Pengembangan Diri';
                        $path = '/development';
                        $receiverId = $userManager->id;

                        NotificationFacade::send($userManager, new DevelopmentNotification($dataNotif, $path, $type, $receiverId));
                    }
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }

        // 5. Pesan Feedback disesuaikan
        $message = ($statusApproval === 'approved')
            ? 'Data sertifikasi berhasil disimpan.'
            : 'Pengajuan sertifikasi berhasil dikirim menunggu persetujuan.';

        return redirect()->back()->with('success', $message);
    }

    public function storePelatihan(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nama_pelatihan'  => 'required|string|max:255',
            // 'penyedia'        => 'required|string|max:255',
            'vendor'          => 'required|string|max:255',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'nullable|string',
            'harga'           => 'required|numeric|min:0',
            'bukti_pelatihan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Validasi tambahan jika toggle sertifikasi aktif
        if ($request->has('is_sertifikasi') && $request->is_sertifikasi == '1') {
            $request->validate([
                'nama_sertifikat_manual' => 'required|string|max:255',
                'tgl_ujian_sertifikasi'  => 'nullable|date',
            ]);
        }

        try {
            DB::beginTransaction();

            // Handle File Upload
            $filePath = null;
            if ($request->hasFile('bukti_pelatihan')) {
                $filePath = $request->file('bukti_pelatihan')->store('bukti_pelatihan', 'public');
            }

            $sertifikasiId = null;
            $namaSertifikasiTambahan = null;

            // 2. Buat Sertifikasi (Jika Toggle Aktif)
            if ($request->has('is_sertifikasi') && $request->is_sertifikasi == '1') {
                $sertifikasi = Sertifikasi::create([
                    'user_id'                => Auth::id(),
                    'nama_sertifikat'        => $request->nama_sertifikat_manual,
                    'tanggal_ujian'          => $request->tgl_ujian_sertifikasi,
                    // 'penyedia'               => $request->penyedia,
                    'vendor'                 => $request->vendor,
                    'harga'                  => $request->harga,
                    'keterangan'             => $request->keterangan,
                    'tanggal_berlaku_dari'   => null,
                    'tanggal_berlaku_sampai' => null,
                    'status_approval'        => 'pending',
                ]);

                $sertifikasiId = $sertifikasi->id;
                $namaSertifikasiTambahan = $sertifikasi->nama_sertifikat; // Simpan nama untuk notifikasi
            }

            // 3. Buat Pelatihan
            $pelatihan = Pelatihan::create([
                'user_id'           => Auth::id(),
                'nama_pelatihan'    => $request->nama_pelatihan,
                // 'penyedia'          => $request->penyedia,
                'vendor'            => $request->vendor,
                'tanggal_mulai'     => $request->tanggal_mulai,
                'tanggal_selesai'   => $request->tanggal_selesai,
                'keterangan'        => $request->keterangan,
                'harga'             => $request->harga,
                'status_approval'   => 'pending',
                'bukti_pelatihan'   => $filePath,
                'id_sertifikasi'    => $sertifikasiId,
            ]);

            // 4. Kirim Notifikasi ke Education Manager
            $managers = karyawan::where('jabatan', 'Education Manager')->get();
            foreach ($managers as $manager) {
                $userManager = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $manager->kode_karyawan))->first();

                // Pastikan user ditemukan dan bukan diri sendiri (jika pengaju adalah manager itu sendiri)
                if ($userManager && $userManager->id !== Auth::id()) {

                    $dataNotif = [
                        'id_user'           => Auth::id(),
                        'tipe_kategori'     => 'Pelatihan',
                        'nama_item'         => $pelatihan->nama_pelatihan,

                        // TAMBAHAN: Sertakan nama sertifikasi jika ada
                        'nama_sertifikasi_tambahan' => $namaSertifikasiTambahan,

                        'tanggal_pengajuan' => now(),
                        'tanggal_mulai'     => $pelatihan->tanggal_mulai,
                        'tanggal_selesai'   => $pelatihan->tanggal_selesai,
                        'harga'             => $pelatihan->harga,
                    ];

                    $type = 'Mengajukan Pengembangan Diri';
                    $path = '/development';
                    $receiverId = $userManager->id;

                    NotificationFacade::send($userManager, new DevelopmentNotification($dataNotif, $path, $type, $receiverId));
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan pelatihan: ' . $e->getMessage())->withInput();
        }

        return redirect()->back()->with('success', 'Pelatihan berhasil disimpan' . ($sertifikasiId ? ' dan Sertifikasi berhasil dibuat.' : '.'));
    }

    public function approveSertifikasi(Request $request, $id)
    {
        if (auth()->user()->karyawan->jabatan !== 'Education Manager') {
            abort(403, 'Unauthorized');
        }

        try {
            DB::beginTransaction();

            $sertifikasi = Sertifikasi::with('user.karyawan')->findOrFail($id);
            $status = $request->status_approval;

            // --- LOGIKA DETEKSI RENEWAL YANG LEBIH KUAT ---
            // 1. Cek History: Apakah sudah pernah punya ID Pengajuan Barang?
            $hasHistory = !is_null($sertifikasi->id_pengajuan_barang);

            // 2. Cek Intent: Apakah di keterangan ada kata 'Perpanjangan'?
            $hasKeyword = stripos($sertifikasi->keterangan, 'Perpanjangan') !== false;

            // Jika salah satu terpenuhi, anggap sebagai renewal
            $isRenewal = $hasHistory || $hasKeyword;

            // Update Status Sertifikasi
            $sertifikasi->update([
                'status_approval' => $status,
                'approved_by'     => Auth::id(),
                'approved_at'     => now(),
            ]);

            if ($status === 'approved') {
                $karyawan = $sertifikasi->user->karyawan;

                if ($karyawan) {
                    // A. Buat Header Pengajuan Barang BARU
                    $pengajuan = PengajuanBarang::create([
                        'id_karyawan' => $karyawan->id,
                        'tipe'        => 'Training & Sertifikasi',
                        'invoice'     => null,
                        'id_tracking' => 0,
                    ]);

                    // B. Update relasi ke ID Pengajuan yang baru
                    $sertifikasi->update([
                        'id_pengajuan_barang' => $pengajuan->id
                    ]);

                    // C. MODIFIKASI NAMA BARANG
                    $namaBarang = $sertifikasi->nama_sertifikat;

                    if ($isRenewal) {
                        // Tambahkan suffix (Perpanjang)
                        $namaBarang .= ' (Perpanjang)';
                    }

                    $keteranganDetail = 'Sertifikasi: ' . $sertifikasi->nama_sertifikat .
                                        ' via ' . $sertifikasi->vendor .
                                        ' (Tanggal: ' . \Carbon\Carbon::parse($sertifikasi->tanggal_ujian)->format('d M Y') . ')';

                    // D. Simpan Detail dengan Nama yang sudah dimodifikasi
                    detailPengajuanBarang::create([
                        'id_pengajuan_barang' => $pengajuan->id,
                        'nama_barang'         => $namaBarang, // <--- Pastikan variabel ini yang dipakai
                        'qty'                 => 1,
                        'harga'               => $sertifikasi->harga,
                        'keterangan'          => $keteranganDetail,
                    ]);

                    // E. Tracking & Notifikasi (Tetap sama)
                    tracking_pengajuan_barang::create([
                        'id_pengajuan_barang' => $pengajuan->id,
                        'tracking'            => 'Diajukan dan Sedang Ditinjau oleh Education Manager',
                        'tanggal'             => $sertifikasi->created_at,
                    ]);

                    $trackingTerbaru = tracking_pengajuan_barang::create([
                        'id_pengajuan_barang' => $pengajuan->id,
                        'tracking'            => 'Telah disetujui oleh Education Manager dan sedang diproses oleh Finance',
                        'tanggal'             => now(),
                    ]);

                    $pengajuan->update(['id_tracking' => $trackingTerbaru->id]);

                    // Notifikasi Finance
                    $finances = karyawan::where('jabatan', 'Finance & Accounting')->get();
                    foreach ($finances as $finance) {
                        $userFinance = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $finance->kode_karyawan))->first();
                        if ($userFinance) {
                            NotificationFacade::send($userFinance, new ApprovalbarangNotification(
                                ['tanggal' => now(), 'status' => 'Menunggu Proses Finance'],
                                '/pengajuanbarang',
                                $karyawan->nama_lengkap,
                                'Training & Sertifikasi',
                                $userFinance->id
                            ));
                        }
                    }
                }
            }

            // Notifikasi Balik ke User
            $pengaju = $sertifikasi->user;
            if ($pengaju) {
                $notifType = ($status == 'approved') ? 'Pengembangan Diri Disetujui' : 'Pengembangan Diri Ditolak';
                $pesanStatus = ($status == 'approved') ? 'Disetujui & Diproses Finance' : 'Ditolak';

                // Nama item di notifikasi juga ikut berubah
                $namaNotif = $sertifikasi->nama_sertifikat . ($isRenewal ? ' (Perpanjang)' : '');

                $dataNotif = [
                    'tipe_kategori'     => 'Sertifikasi',
                    'nama_item'         => $namaNotif,
                    'status'            => 'Status diubah menjadi: ' . $pesanStatus,
                    'tanggal_pengajuan' => now(),
                    'tanggal_ujian'     => $sertifikasi->tanggal_ujian,
                    'harga'             => $sertifikasi->harga,
                ];

                NotificationFacade::send($pengaju, new DevelopmentNotification($dataNotif, '/development', $notifType, $pengaju->id));
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update status: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Status Sertifikasi diperbarui.');
    }

    public function approvePelatihan(Request $request, $id)
    {
        if (auth()->user()->karyawan->jabatan !== 'Education Manager') {
            abort(403, 'Unauthorized');
        }

        try {
            DB::beginTransaction();

            $pelatihan = Pelatihan::with('user.karyawan')->findOrFail($id);
            $status = $request->status_approval;

            // 1. Update Status Pelatihan
            $pelatihan->update([
                'status_approval' => $status,
                'approved_by'     => Auth::id(),
                'approved_at'     => now(),
            ]);

            // 2. Sinkronisasi Status Sertifikasi (Jika Ada)
            if ($pelatihan->id_sertifikasi) {
                Sertifikasi::where('id', $pelatihan->id_sertifikasi)->update([
                    'status_approval' => $status,
                    'approved_by'     => Auth::id(),
                    'approved_at'     => now(),
                ]);
            }

            // 3. Logika Jika Approved (Buat Pengajuan Barang & Tracking)
            if ($status === 'approved') {
                $karyawan = $pelatihan->user->karyawan;

                if ($karyawan) {
                    // Buat Header Pengajuan Barang
                    $pengajuan = PengajuanBarang::create([
                        'id_karyawan' => $karyawan->id,
                        'tipe'        => 'Training & Sertifikasi',
                        'invoice'     => null,
                        'id_tracking' => 0,
                    ]);

                    // Update relasi di pelatihan
                    $pelatihan->update([
                        'id_pengajuan_barang' => $pengajuan->id
                    ]);

                    $infoDetail = 'Pelatihan: ' . $pelatihan->nama_pelatihan;

                    if ($pelatihan->id_sertifikasi) {
                        $dataSertifikasi = Sertifikasi::find($pelatihan->id_sertifikasi);
                        $namaSertifikat = $dataSertifikasi ? $dataSertifikasi->nama_sertifikat : '-';

                        $infoDetail .= ' & Sertifikasi: ' . $namaSertifikat;
                    }

                    $keteranganDetail = $infoDetail . ' via ' . $pelatihan->vendor .
                                        ' (' . Carbon::parse($pelatihan->tanggal_mulai)->format('d M Y') .
                                        ' - ' . Carbon::parse($pelatihan->tanggal_selesai)->format('d M Y') . ')';

                    detailPengajuanBarang::create([
                        'id_pengajuan_barang' => $pengajuan->id,
                        'nama_barang'         => $pelatihan->nama_pelatihan,
                        'qty'                 => 1,
                        'harga'               => $pelatihan->harga,
                        'keterangan'          => $keteranganDetail,
                    ]);

                    tracking_pengajuan_barang::create([
                        'id_pengajuan_barang' => $pengajuan->id,
                        'tracking'            => 'Diajukan dan Sedang Ditinjau oleh Education Manager',
                        'tanggal'             => $pelatihan->created_at,
                    ]);

                    $trackingTerbaru = tracking_pengajuan_barang::create([
                        'id_pengajuan_barang' => $pengajuan->id,
                        'tracking'            => 'Telah disetujui oleh Education Manager dan sedang diproses oleh Finance',
                        'tanggal'             => now(),
                    ]);

                    // Update Header Tracking ID
                    $pengajuan->update(['id_tracking' => $trackingTerbaru->id]);

                    // Notifikasi ke Finance
                    $finances = karyawan::where('jabatan', 'Finance & Accounting')->get();
                    foreach ($finances as $finance) {
                        $userFinance = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $finance->kode_karyawan))->first();
                        if ($userFinance) {
                            $dataNotifFinance = [
                                'tanggal' => now(),
                                'status'  => 'Menunggu Proses Finance',
                            ];
                            NotificationFacade::send($userFinance, new ApprovalbarangNotification(
                                $dataNotifFinance,
                                '/pengajuanbarang',
                                $karyawan->nama_lengkap,
                                'Training & Sertifikasi',
                                $userFinance->id
                            ));
                        }
                    }
                }
            }

            // 4. Notifikasi Balik ke User
            $pengaju = $pelatihan->user;
            if ($pengaju) {
                $notifType = ($status == 'approved') ? 'Pengembangan Diri Disetujui' : 'Pengembangan Diri Ditolak';
                $pesanStatus = ($status == 'approved') ? 'Disetujui & Diproses Finance' : 'Ditolak';

                $dataNotif = [
                    'tipe_kategori'     => 'Pelatihan',
                    'nama_item'         => $pelatihan->nama_pelatihan,
                    'status'            => 'Status diubah menjadi: ' . $pesanStatus,
                    'tanggal_pengajuan' => now(),
                    'tanggal_mulai'     => $pelatihan->tanggal_mulai,
                    'tanggal_selesai'   => $pelatihan->tanggal_selesai,
                    'harga'             => $pelatihan->harga,
                ];
                NotificationFacade::send($pengaju, new DevelopmentNotification($dataNotif, '/development', $notifType, $pengaju->id));
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update status: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Status Pelatihan diperbarui' . ($status == 'approved' ? ', data diteruskan ke Finance.' : '.'));
    }

    public function destroySertifikasi($id)
    {
        $data = Sertifikasi::where('user_id', Auth::id())->findOrFail($id);
        if ($data->status_approval === 'approved') return back()->with('error', 'Gagal hapus data yang sudah disetujui.');
        $data->delete();
        return back()->with('success', 'Sertifikasi dihapus.');
    }

    public function destroyPelatihan($id)
    {
        $data = Pelatihan::where('user_id', Auth::id())->findOrFail($id);
        if ($data->status_approval === 'approved') return back()->with('error', 'Gagal hapus data yang sudah disetujui.');
        $data->delete();
        return back()->with('success', 'Pelatihan dihapus.');
    }

    public function updatePelatihan(Request $request, $id)
    {
        $pelatihan = Pelatihan::where('user_id', Auth::id())->findOrFail($id);

        if ($pelatihan->status_approval === 'approved') {
            return redirect()->back()->with('error', 'Data yang sudah disetujui tidak dapat diedit.');
        }

        $request->validate([
            'nama_pelatihan'  => 'required|string|max:255',
            // 'penyedia'        => 'required|string|max:255',
            'vendor'          => 'required|string|max:255',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'nullable|string',
            'harga'           => 'required|numeric|min:0',
        ]);

        $pelatihan->update($request->all());

        return redirect()->back()->with('success', 'Pelatihan berhasil diperbarui.');
    }

    public function updateSertifikasi(Request $request, $id)
    {
        $sertifikasi = Sertifikasi::where('user_id', Auth::id())->findOrFail($id);

        // if ($sertifikasi->status_approval === 'approved') {
        //     return redirect()->back()->with('error', 'Data yang sudah disetujui tidak dapat diedit.');
        // }

        $request->validate([
            'nama_sertifikat'        => 'required|string|max:255',
            // 'penyedia'               => 'required|string|max:255',
            'tanggal_ujian'          => 'required|date',
            'tanggal_berlaku_dari'   => 'nullable|date',
            'tanggal_berlaku_sampai' => 'nullable|date|after_or_equal:tanggal_berlaku_dari',
            'harga'                  => 'required|numeric|min:0',
            'vendor'                 => 'required|string|max:255',
        ]);

        $sertifikasi->update($request->all());

        return redirect()->back()->with('success', 'Sertifikasi berhasil diperbarui.');
    }

    public function uploadBukti(Request $request, $id)
    {
        $pelatihan = Pelatihan::where('user_id', Auth::id())->findOrFail($id);

        if ($pelatihan->status_approval !== 'approved') {
            return redirect()->back()->with('error', 'Anda hanya dapat mengupload bukti jika pelatihan sudah disetujui.');
        }

        $request->validate([
            'bukti_pelatihan' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            if ($pelatihan->bukti_pelatihan && Storage::disk('public')->exists($pelatihan->bukti_pelatihan)) {
                Storage::disk('public')->delete($pelatihan->bukti_pelatihan);
            }
            $path = $request->file('bukti_pelatihan')->store('bukti_pelatihan', 'public');

            $pelatihan->update([
                'bukti_pelatihan' => $path
            ]);
            return redirect()->back()->with('success', 'Bukti pelatihan berhasil diupload.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupload file: ' . $e->getMessage());
        }
    }

    public function uploadBuktiSertifikasi(Request $request, $id)
    {
        $sertifikasi = Sertifikasi::where('user_id', Auth::id())->findOrFail($id);

        if ($sertifikasi->status_approval !== 'approved') {
            return redirect()->back()->with('error', 'Anda hanya dapat mengupload bukti jika sertifikasi sudah disetujui.');
        }

        $request->validate([
            'bukti_sertifikasi' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // Max 5MB
        ]);

        try {
            if ($sertifikasi->bukti_sertifikasi && Storage::disk('public')->exists($sertifikasi->bukti_sertifikasi)) {
                Storage::disk('public')->delete($sertifikasi->bukti_sertifikasi);
            }

            $path = $request->file('bukti_sertifikasi')->store('bukti_sertifikasi', 'public');

            $sertifikasi->update([
                'bukti_sertifikasi' => $path
            ]);

            return redirect()->back()->with('success', 'Bukti sertifikasi berhasil diupload.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupload file: ' . $e->getMessage());
        }
    }

    public function storeSpecialization(Request $request)
    {
        $request->validate([
            'specialization'        => 'required|string|max:255',
            'detail_specialization' => 'required|string|max:255',
        ]);

        try {
            $user = Auth::user();
            $kodeInstruktur = $user->karyawan->kode_karyawan ?? null;

            if (!$kodeInstruktur) {
                return redirect()->back()->with('error', 'Gagal: Kode Instruktur/Karyawan tidak ditemukan.');
            }

            // Cek apakah instruktur sudah punya data (karena schema unique)
            $exists = SpecializationArea::where('kode_instruktur', $kodeInstruktur)->exists();
            if ($exists) {
                return redirect()->back()->with('error', 'Gagal: Anda sudah memiliki data Specialization Area (Data harus unik per instruktur). Silakan edit data yang ada.');
            }

            SpecializationArea::create([
                'specialization'        => $request->specialization,
                'detail_specialization' => $request->detail_specialization,
                'kode_instruktur'       => $kodeInstruktur,
            ]);

            return redirect()->back()->with('success', 'Specialization Area berhasil ditambahkan.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function updateSpecialization(Request $request, $id)
    {
        $request->validate([
            'specialization'        => 'required|string|max:255',
            'detail_specialization' => 'required|string|max:255',
        ]);

        try {
            $specialization = SpecializationArea::findOrFail($id);

            // Validasi kepemilikan
            if($specialization->kode_instruktur !== Auth::user()->karyawan->kode_karyawan) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit data ini.');
            }

            $specialization->update([
                'specialization'        => $request->specialization,
                'detail_specialization' => $request->detail_specialization,
            ]);

            return redirect()->back()->with('success', 'Specialization Area berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroySpecialization($id)
    {
        try {
            $specialization = SpecializationArea::findOrFail($id);

            // Validasi kepemilikan
            if($specialization->kode_instruktur !== Auth::user()->karyawan->kode_karyawan) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus data ini.');
            }

            $specialization->delete();

            return redirect()->back()->with('success', 'Specialization Area berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function storeRenewal(Request $request, $id)
    {
        $sertifikasi = Sertifikasi::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'tanggal_ujian'          => 'required|date',
            'tanggal_berlaku_dari'   => 'nullable|date',
            'tanggal_berlaku_sampai' => 'nullable|date|after_or_equal:tanggal_berlaku_dari',
            'harga'                  => 'required|numeric|min:0',
            'keterangan'             => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // PASTIKAN keterangan mengandung kata kunci agar mudah dideteksi saat approval
            $keterangan = $request->keterangan;
            if (stripos($keterangan, 'Perpanjangan') === false) {
                $keterangan = 'Perpanjangan. ' . $keterangan;
            }

            $sertifikasi->update([
                'tanggal_ujian'          => $request->tanggal_ujian,
                'tanggal_berlaku_dari'   => $request->tanggal_berlaku_dari,
                'tanggal_berlaku_sampai' => $request->tanggal_berlaku_sampai,
                'harga'                  => $request->harga,
                'keterangan'             => $keterangan, // Keterangan sudah ada keyword
                'status_approval'        => 'pending',
                'approved_by'            => null,
                'approved_at'            => null,
            ]);

            // ... (Kode notifikasi tetap sama) ...
            $managers = karyawan::where('jabatan', 'Education Manager')->get();
            foreach ($managers as $manager) {
                $userManager = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $manager->kode_karyawan))->first();
                if ($userManager) {
                    $dataNotif = [
                        'id_user'           => Auth::id(),
                        'tipe_kategori'     => 'Sertifikasi (Perpanjangan)',
                        'nama_item'         => $sertifikasi->nama_sertifikat,
                        'tanggal_pengajuan' => now(),
                        'tanggal_ujian'     => $sertifikasi->tanggal_ujian,
                        'berlaku_dari'      => $sertifikasi->tanggal_berlaku_dari,
                        'berlaku_sampai'    => $sertifikasi->tanggal_berlaku_sampai,
                        'harga'             => $sertifikasi->harga,
                    ];
                    NotificationFacade::send($userManager, new DevelopmentNotification($dataNotif, '/development', 'Mengajukan Pengembangan Diri', $userManager->id));
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengajukan perpanjangan: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Pengajuan perpanjangan dikirim.');
    }
}
