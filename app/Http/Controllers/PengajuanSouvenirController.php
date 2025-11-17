<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSouvenir;
use App\Models\TrackingPengajuanSouvenir;
use App\Models\karyawan; // Asumsi dari contoh
use App\Models\User;     // Asumsi dari contoh
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\PengajuanSouvenirNotification; // HARUS DIBUAT
use App\Notifications\ApprovalSouvenirNotification;  // HARUS DIBUAT

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
        $tracking = 'buka'; // Default untuk GM dan Finance

        // Hanya Customer Care yang punya batasan 'buka'/'tutup'
        if ($jabatan === 'Customer Care') {
            $trackingRecord = TrackingPengajuanSouvenir::whereHas('pengajuanSouvenir.karyawan', function ($query) use ($user) {
                $query->where('id', $user->karyawan_id);
            })
            ->latest()
            ->first();

            $tracking = $this->determineTrackingStatus($trackingRecord);
        }

        return view('pengajuansouvenir.index', compact('tracking'));
    }

    /**
     * Fungsi untuk menentukan status tracking (Buka/Tutup)
     */
    private function determineTrackingStatus($trackingRecord)
    {
        if (is_null($trackingRecord)) {
            return 'buka'; // Belum pernah mengajukan
        }

        // Jika status terakhir adalah Selesai atau Ditolak, boleh buat baru.
        if (
            $trackingRecord->tracking === 'Pencairan Selesai' ||
            str_starts_with($trackingRecord->tracking, 'Ditolak')
        ) {
            return 'buka';
        }

        // Jika masih 'Menunggu' atau 'Disetujui', maka 'tutup'
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

        $query = PengajuanSouvenir::with('karyawan', 'tracking')
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year);

        if ($jabatan === 'Customer Care') {
            // Customer Care hanya melihat pengajuan miliknya
            $query->where('id_karyawan', $userKaryawan->id);
        } elseif ($jabatan === 'GM' || $jabatan === 'Finance & Accounting') {
            // GM dan Finance melihat semua
        } else {
            // Jabatan lain tidak bisa melihat
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

        // Pastikan hanya Customer Care yang bisa akses form create
        if ($karyawan->jabatan !== 'Customer Care') {
             return redirect()->route('pengajuansouvenir.index')->with('error', 'Hanya Customer Care yang dapat mengakses halaman ini.');
        }

        return view('pengajuansouvenir.create', compact('karyawan'));
    }

    /**
     * Menyimpan Pengajuan Souvenir baru ke dalam database.
     * Alur: CC -> GM
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|exists:karyawans,id',
            'id_vendor' => 'required',
            'id_souvenir' => 'required',
            'pax' => 'required|numeric|min:1',
            'harga_satuan' => 'required|numeric|min:0',
            'harga_total' => 'required|numeric|min:0',
        ]);

        $karyawan = karyawan::findOrFail($request->id_karyawan);
        if ($karyawan->jabatan !== 'Customer Care') {
            return redirect()->back()->with('error', 'Hanya Customer Care yang dapat membuat pengajuan.');
        }

        $pengajuan = PengajuanSouvenir::create($request->all());

        // 1. Buat status tracking awal
        $statusAwal = 'Diajukan, Menunggu Persetujuan GM';
        $tracking = TrackingPengajuanSouvenir::create([
            'id_pengajuan_souvenir' => $pengajuan->id,
            'tracking' => $statusAwal,
            'tanggal' => now(),
        ]);

        // 2. Update id_tracking di pengajuan utama
        $pengajuan->update([
            'id_tracking' => $tracking->id,
        ]);

        // 3. Kirim Notifikasi ke GM (Mengikuti pola PengajuanBarang)
        $gm = karyawan::where('jabatan', 'GM')->first();
        $usersToNotifyCodes = [];
        if ($gm) {
            $usersToNotifyCodes[] = $gm->kode_karyawan;
        }

        $users = User::whereHas('karyawan', function ($query) use ($usersToNotifyCodes) {
            $query->whereIn('kode_karyawan', array_filter($usersToNotifyCodes));
        })->get();

        $data = [
            'id_karyawan' => $request->id_karyawan,
            'tipe' => 'Souvenir', // Tipe bisa di-hardcode
            'tanggal_pengajuan' => now()
        ];
        $type = 'Mengajukan Permintaan Souvenir';
        $path = '/pengajuansouvenir';

        foreach ($users as $user) {
            NotificationFacade::send($user, new PengajuanSouvenirNotification($data, $path, $type));
        }

        return redirect()->route('pengajuansouvenir.index')->with('success', 'Pengajuan Souvenir berhasil dibuat.');
    }

    /**
     * Menampilkan detail Pengajuan Souvenir tertentu.
     */
    public function show($id)
    {
        $data = PengajuanSouvenir::with('karyawan', 'tracking')->findOrFail($id);
        $tracking = TrackingPengajuanSouvenir::where('id_pengajuan_souvenir', $id)->orderBy('created_at', 'asc')->get();
        return view('pengajuansouvenir.show', compact('data', 'tracking'));
    }

    /**
     * Menampilkan form untuk mengedit Pengajuan Souvenir.
     */
    public function edit($id)
    {
        $pengajuan = PengajuanSouvenir::findOrFail($id);
        $karyawan = karyawan::findOrFail($pengajuan->id_karyawan);

        // Logika: Hanya boleh edit jika ditolak atau masih menunggu?
        $statusTerakhir = $pengajuan->tracking->tracking;
        if (!str_starts_with($statusTerakhir, 'Ditolak') && $statusTerakhir !== 'Diajukan, Menunggu Persetujuan GM') {
           return redirect()->route('pengajuansouvenir.show', $id)->with('error', 'Data tidak dapat diedit karena sedang diproses.');
        }

        if (auth()->user()->karyawan_id !== $pengajuan->id_karyawan) {
            return redirect()->route('pengajuansouvenir.index')->with('error', 'Anda tidak berhak mengedit pengajuan ini.');
        }

        return view('pengajuansouvenir.edit', compact('pengajuan', 'karyawan'));
    }

    /**
     * Memperbarui Pengajuan Souvenir (Approval / Penolakan).
     */
    public function update(Request $request, $id)
    {
        $pengajuan = PengajuanSouvenir::with('karyawan', 'tracking')->findOrFail($id);
        $jabatan = auth()->user()->karyawan->jabatan;
        $status = '';
        $notifType = ''; // Tipe notifikasi (Setuju/Tolak)

        // --- Kumpulkan semua user yang mungkin terlibat ---
        $karyawanCC = $pengajuan->karyawan;
        $userCC = User::where('karyawan_id', $karyawanCC->id)->first();

        $gm = karyawan::where('jabatan', 'GM')->first();
        $userGM = $gm ? User::where('karyawan_id', $gm->id)->first() : null;

        $finance = karyawan::where('jabatan', 'Finance & Accounting')->first();
        $userFinance = $finance ? User::where('karyawan_id', $finance->id)->first() : null;

        // Daftar user yang akan dikirimi notifikasi
        $usersToNotify = [];
        if ($userCC) $usersToNotify[] = $userCC; // Pembuat selalu dinotifikasi

        // Alur approval == 1 (Setuju) atau 2 (Tolak)
        $approval = $request->input('approval');
        $alasan = $request->input('alasan', '');

        if ($approval == '2') { // --- PROSES TOLAK ---
            $notifType = 'Pengajuan Souvenir Ditolak';
            if ($jabatan === 'GM') {
                $status = 'Ditolak GM: ' . $alasan;
            } elseif ($jabatan === 'Finance & Accounting') {
                $status = 'Pencairan Ditolak Finance: ' . $alasan;
                if ($userGM) $usersToNotify[] = $userGM; // GM juga diinfo
            } else {
                return redirect()->route('pengajuansouvenir.index')->with('error', 'Anda tidak memiliki wewenang untuk menolak.');
            }

        } elseif ($approval == '1') { // --- PROSES SETUJU ---
            $notifType = 'Pengajuan Souvenir Disetujui';

            // TAHAP 1: Approval oleh GM
            if ($jabatan === 'GM') {
                if ($pengajuan->tracking->tracking === 'Diajukan, Menunggu Persetujuan GM') {
                    $status = 'Disetujui GM, Menunggu Pencairan Finance';
                    if ($userFinance) $usersToNotify[] = $userFinance; // Notif ke Finance
                } else {
                    return redirect()->route('pengajuansouvenir.show', $id)->with('error', 'Status pengajuan sudah berubah.');
                }
            }

            // TAHAP 2: Approval (Pencairan) oleh Finance
            elseif ($jabatan === 'Finance & Accounting') {
                 if ($pengajuan->tracking->tracking === 'Disetujui GM, Menunggu Pencairan Finance') {

                    // Ambil status dari dropdown
                    $status = $request->input('status');

                    if (empty($status)) {
                        $status = 'Pencairan Selesai'; // Fallback
                    }

                    if ($userGM) $usersToNotify[] = $userGM; // Notif ke GM
                } else {
                    return redirect()->route('pengajuansouvenir.show', $id)->with('error', 'Status pengajuan tidak valid untuk pencairan.');
                }
            }

            else {
                return redirect()->route('pengajuansouvenir.index')->with('error', 'Anda tidak memiliki wewenang untuk menyetujui.');
            }

        } else {
             // Jika ini adalah update data biasa (misal dari form edit)
             // (Tambahkan logika ini jika Anda mengizinkan edit setelah ditolak)
             $pengajuan->update($request->except(['_token', '_method', 'approval', 'alasan', 'status']));
             return redirect()->route('pengajuansouvenir.show', $id)->with('success', 'Data berhasil diperbarui.');
        }

        // Jika ada status baru, buat record tracking dan kirim notifikasi
        if ($status && $notifType) {
            $tracking = TrackingPengajuanSouvenir::create([
                'id_pengajuan_souvenir' => $id,
                'tracking' => $status,
                'tanggal' => now()
            ]);
            $pengajuan->update(['id_tracking' => $tracking->id]);

            // Kirim Notifikasi (Mengikuti pola ApprovalBarang)
            $notifData = ['tanggal' => now(), 'status' => $status];
            $path = '/pengajuansouvenir';
            $to = $karyawanCC->nama_lengkap; // Target nama di notifikasi

            // Filter user unik dan kirim notifikasi
            $uniqueUsers = array_filter(array_unique($usersToNotify));
            foreach ($uniqueUsers as $user) {
                if($user) {
                    NotificationFacade::send($user, new ApprovalSouvenirNotification($notifData, $path, $to, $notifType));
                }
            }
        }

        return redirect()->route('pengajuansouvenir.index')->with('success', 'Status Pengajuan Souvenir berhasil diperbarui.');
    }

    /**
     * Menghapus Pengajuan Souvenir dari database.
     */
    public function destroy($id)
    {
        $data = PengajuanSouvenir::findOrFail($id);

        // Hanya pembuat yang bisa hapus, atau admin/GM?
        if (auth()->user()->karyawan_id !== $data->id_karyawan && auth()->user()->karyawan->jabatan !== 'GM') {
             return redirect()->route('pengajuansouvenir.index')->with('error', 'Anda tidak berhak menghapus data ini.');
        }

        // Hapus semua tracking yang terkait
        TrackingPengajuanSouvenir::where('id_pengajuan_souvenir', $id)->delete();

        // Hapus data pengajuan utama
        $data->delete();

        return redirect()->route('pengajuansouvenir.index')->with('success', 'Pengajuan Souvenir berhasil dihapus!');
    }
}
