<?php

namespace App\Http\Controllers;

use App\Models\PenambahanSouvenir;
use App\Models\souvenir;
use App\Models\RKM;
use App\Models\karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\PenambahanSouvenirNotification;
use Carbon\Carbon;

class PenambahanSouvenirController extends Controller
{
    /**
     * Menampilkan daftar distribusi souvenir.
     * Hanya bisa diakses oleh Customer Care dan GM.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->karyawan || !$user->karyawan->jabatan) {
            return redirect()->route('login');
        }

        $jabatan = $user->karyawan->jabatan;

        // VALIDASI AKSES HALAMAN: Hanya Customer Care dan GM
        if ($jabatan !== 'Customer Care' && $jabatan !== 'GM') {
             return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Return view saja, data akan diambil via AJAX (getPenambahanSouvenir)
        return view('penambahansouvenir.index');
    }

    /**
     * API untuk mengambil data penambahan souvenir (untuk DataTables).
     * Filter berdasarkan Bulan dan Tahun.
     */
    public function getPenambahanSouvenir($month, $year)
    {
        $userKaryawan = auth()->user()->karyawan;
        if (!$userKaryawan) {
            return response()->json(['data' => []], 401);
        }

        $jabatan = $userKaryawan->jabatan;

        // VALIDASI AKSES DATA: Hanya Customer Care dan GM
        if ($jabatan === 'Customer Care' || $jabatan === 'GM') {
            // Lanjut proses query
        } else {
            // Jabatan lain tidak dapat melihat data
            return response()->json(['data' => []]);
        }

        $query = PenambahanSouvenir::with(['souvenir', 'rkm.materi'])
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year);

        $data = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'List Penambahan Souvenir',
            'data' => $data,
        ]);
    }

    /**
     * Form tambah data.
     */
    public function create()
    {
        $user = auth()->user();
        // Pastikan relasi user ke karyawan ada
        if (!$user->karyawan) {
            return redirect()->back()->with('error', 'Akun Anda tidak terhubung dengan data Karyawan.');
        }

        $karyawan = $user->karyawan;

        // Filter RKM: 1 minggu lalu s/d 3 minggu ke depan
        $startDate = now()->subWeek()->startOfDay();
        $endDate   = now()->addWeeks(3)->endOfDay();

        // Eager Load 'materi' untuk menghindari N+1 Query di View
        $rkms = RKM::with('materi')
                    ->whereBetween('tanggal_awal', [$startDate, $endDate])
                    ->orderBy('tanggal_awal', 'asc')
                    ->get();

        $souvenirs = Souvenir::where('stok', '>', 0)->get();

        return view('penambahansouvenir.create', compact('souvenirs', 'rkms', 'karyawan'));
    }

    /**
     * Simpan data
     */
    public function store(Request $request)
    {
        // 1. Validasi
        $request->validate([
            // Sesuaikan nama tabel RKM jika berbeda (rkms atau r_k_m_s)
            'id_rkm' => 'required|exists:r_k_m_s,id',
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'souvenir_id' => 'required|array|min:1',
            'souvenir_id.*' => 'required|exists:souvenirs,id',
            'qty' => 'required|array|min:1',
            'qty.*' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Ambil ID Karyawan dari sesi login (Secure)
            $karyawanId = auth()->user()->karyawan->id;

            $items = $request->souvenir_id;
            $quantities = $request->qty;

            foreach ($items as $index => $souvenirId) {
                $qtyRequested = $quantities[$index];

                // Lock row untuk mencegah race condition stok
                $souvenir = Souvenir::lockForUpdate()->find($souvenirId);

                if (!$souvenir || $souvenir->stok < $qtyRequested) {
                    throw new \Exception("Stok " . ($souvenir->nama_souvenir ?? 'Item') . " kurang. Sisa: " . ($souvenir->stok ?? 0));
                }

                // Simpan Data (Termasuk id_karyawan)
                PenambahanSouvenir::create([
                    'id_rkm' => $request->id_rkm,
                    'id_karyawan' => $karyawanId, // <--- DATA BARU DITAMBAHKAN
                    'id_souvenir' => $souvenirId,
                    'nama' => $request->nama,       // Nama Penerima
                    'jabatan' => $request->jabatan, // Jabatan Penerima
                    'qty' => $qtyRequested,
                    'tanggal' => $request->tanggal,
                ]);

                // Kurangi Stok
                $souvenir->decrement('stok', $qtyRequested);
            }

            DB::commit();

            $rkm = RKM::with('materi')->find($request->id_rkm);

            // 2. Tentukan Nama Materi (Fallback ke nama program jika materi null)
            $namaMateri = $rkm->materi->nama_materi ?? $rkm->nama_program ?? 'RKM #' . $rkm->id;

            $dataNotif = [
                'id_karyawan' => $karyawanId,
                'tipe' => 'Souvenir',
                'tanggal_pengajuan' => $request->tanggal,

                'nama_rkm' => $namaMateri,
                'rkm_start' => $rkm->tanggal_awal,
                'rkm_end' => $rkm->tanggal_akhir
            ];

            $type = 'Laporan Distribusi Souvenir';
            $path = '/penambahansouvenir';

            // Kirim Notif ke GM
            $gm = karyawan::where('jabatan', 'GM')->first();
            if ($gm) {
                $userGM = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $gm->kode_karyawan))->first();
                if ($userGM) NotificationFacade::send($userGM, new PenambahanSouvenirNotification($dataNotif, $path, $type));
            }

            // Kirim Notif ke Diri Sendiri
            NotificationFacade::send(auth()->user(), new PenambahanSouvenirNotification($dataNotif, $path, $type));

            return redirect()->route('penambahansouvenir.index')->with('success', 'Data distribusi berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan form edit untuk satu item distribusi.
     */
    public function edit($id)
    {
        $penambahan = PenambahanSouvenir::with(['rkm', 'souvenir'])->findOrFail($id);

        // Validasi Akses: Hanya pemilik data atau GM/Admin yang bisa edit (Opsional)
        // if (auth()->user()->karyawan->id !== $penambahan->id_karyawan) { ... }

        // Ambil data pendukung
        $souvenirs = Souvenir::where('stok', '>', 0)
                        ->orWhere('id', $penambahan->id_souvenir) // Tetap tampilkan souvenir lama meski stok 0
                        ->get();

        // Ambil RKM (Logic sama seperti create: 1 minggu lalu s/d 3 minggu kedepan)
        // ATAU sertakan juga RKM yang sedang dipakai saat ini agar tidak error di dropdown
        $startDate = now()->subWeek()->startOfDay();
        $endDate   = now()->addWeeks(3)->endOfDay();

        $rkms = RKM::with('materi')
                    ->whereBetween('tanggal_awal', [$startDate, $endDate])
                    ->orWhere('id', $penambahan->id_rkm) // Pastikan RKM saat ini tetap ada di list
                    ->orderBy('tanggal_awal', 'asc')
                    ->get();

        return view('penambahansouvenir.edit', compact('penambahan', 'souvenirs', 'rkms'));
    }

    /**
     * Update data dan sesuaikan stok otomatis.
     */
    public function update(Request $request, $id)
    {
        // 1. Validasi
        $request->validate([
            'id_rkm' => 'required|exists:r_k_m_s,id',
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'id_souvenir' => 'required|exists:souvenirs,id', // Hanya single item untuk edit
            'qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Load Data Lama
            $penambahan = PenambahanSouvenir::findOrFail($id);

            // Simpan data lama untuk perbandingan notifikasi (Opsional)
            $oldData = $penambahan->replicate();

            // === LOGIKA MANAJEMEN STOK (REVERSE & RE-APPLY) ===

            // A. KEMBALIKAN STOK LAMA
            // Kita kembalikan stok souvenir yang lama seolah-olah transaksi dibatalkan dulu.
            $oldSouvenir = Souvenir::lockForUpdate()->find($penambahan->id_souvenir);
            if ($oldSouvenir) {
                $oldSouvenir->increment('stok', $penambahan->qty);
            }

            // B. CEK & POTONG STOK BARU (Bisa jadi itemnya sama, tapi stok sudah updated di langkah A)
            $newSouvenir = Souvenir::lockForUpdate()->find($request->id_souvenir);

            if (!$newSouvenir) {
                throw new \Exception("Souvenir tidak ditemukan.");
            }

            // Cek ketersediaan (Stok saat ini sudah termasuk pengembalian dari langkah A)
            if ($newSouvenir->stok < $request->qty) {
                throw new \Exception("Stok tidak mencukupi untuk update. Stok tersedia: " . $newSouvenir->stok);
            }

            // Potong stok dengan jumlah baru
            $newSouvenir->decrement('stok', $request->qty);

            // === UPDATE DATA TRANSAKSI ===
            $penambahan->update([
                'id_rkm' => $request->id_rkm,
                'id_souvenir' => $request->id_souvenir,
                'nama' => $request->nama,
                'jabatan' => $request->jabatan,
                'qty' => $request->qty,
                'tanggal' => $request->tanggal,
                // 'id_karyawan' tidak diubah agar track record penginput awal tetap terjaga
            ]);

            DB::commit();

            // === KIRIM NOTIFIKASI UPDATE ===
            $this->sendUpdateNotification($penambahan);

            return redirect()->route('penambahansouvenir.index')->with('success', 'Data berhasil diperbarui dan stok telah disesuaikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * Helper Private untuk Notifikasi Update
     */
    private function sendUpdateNotification($penambahan)
    {
        // Ambil nama RKM / Materi
        $rkm = RKM::with('materi')->find($penambahan->id_rkm);
        $namaMateri = $rkm->materi->nama_materi ?? $rkm->nama_program ?? 'RKM #' . $rkm->id;

        $dataNotif = [
            'id_karyawan' => auth()->user()->karyawan->id, // Yang melakukan edit
            'tipe' => 'Souvenir',
            'tanggal_pengajuan' => $penambahan->tanggal,
            'nama_rkm' => $namaMateri,
            'rkm_start' => $rkm->tanggal_awal,
            'rkm_end' => $rkm->tanggal_akhir
        ];

        // Tipe notifikasi berbeda untuk membedakan warna/pesan di view
        $type = 'Pengajuan Souvenir Diperbarui';
        $path = '/penambahansouvenir';

        // Kirim (Contoh ke diri sendiri, bisa disesuaikan ke GM juga)
        NotificationFacade::send(auth()->user(), new PenambahanSouvenirNotification($dataNotif, $path, $type));
    }

}
