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
        // 1. Validasi Input
        $request->validate([
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
            // 2. Persiapan Data
            $karyawanId = auth()->user()->karyawan->id;
            $items = $request->souvenir_id;
            $quantities = $request->qty;

            // Array untuk menampung detail barang notifikasi
            $listBarang = [];

            // 3. Loop Proses Barang
            foreach ($items as $index => $souvenirId) {
                $qtyRequested = $quantities[$index];

                // Lock baris data untuk mencegah pengurangan stok ganda bersamaan
                $souvenir = Souvenir::lockForUpdate()->find($souvenirId);

                // Cek Stok Realtime
                if (!$souvenir || $souvenir->stok < $qtyRequested) {
                    throw new \Exception("Stok " . ($souvenir->nama_souvenir ?? 'Item') . " kurang.");
                }

                // Simpan Riwayat
                PenambahanSouvenir::create([
                    'id_rkm' => $request->id_rkm,
                    'id_karyawan' => $karyawanId,
                    'id_souvenir' => $souvenirId,
                    'nama' => $request->nama,
                    'jabatan' => $request->jabatan,
                    'qty' => $qtyRequested,
                    'tanggal' => $request->tanggal,
                ]);

                // Kurangi Stok Master
                $souvenir->decrement('stok', $qtyRequested);

                // Masukkan ke Array untuk Notifikasi
                $listBarang[] = [
                    'nama_souvenir' => $souvenir->nama_souvenir,
                    'qty' => $qtyRequested
                ];
            }

            // 4. Persiapan Data Notifikasi
            $rkm = RKM::with('materi')->find($request->id_rkm);
            $namaMateri = $rkm->materi->nama_materi ?? $rkm->nama_program ?? 'RKM #' . $rkm->id;

            $dataNotif = [
                'id_karyawan'       => $karyawanId,
                'tipe'              => 'Souvenir',
                'tanggal_pengajuan' => $request->tanggal,
                'nama_rkm'          => $namaMateri,
                'rkm_start'         => $rkm->tanggal_awal,
                'rkm_end'           => $rkm->tanggal_akhir,
                // Data Detail Tambahan
                'penerima_nama'     => $request->nama,
                'penerima_jabatan'  => $request->jabatan,
                'detail_barang'     => $listBarang
            ];

            $type = 'Laporan Distribusi Souvenir';
            $path = '/penambahansouvenir';

            // 5. Kirim Notifikasi

            // A. Ke General Manager (GM)
            $usersGM = User::whereHas('karyawan', function($q) {
                $q->where('jabatan', 'GM');
            })->get();

            foreach ($usersGM as $gm) {
                // Parameter ke-4: ID GM
                $gm->notify(new PenambahanSouvenirNotification($dataNotif, $path, $type, $gm->id));
            }

            // B. Ke Diri Sendiri (Konfirmasi)
            $userSelf = auth()->user();
            if ($userSelf) {
                // Parameter ke-4: ID Sendiri
                $userSelf->notify(new PenambahanSouvenirNotification($dataNotif, $path, $type, $userSelf->id));
            }

            // 6. Commit Database (Simpan permanen)
            DB::commit();

            return redirect()->route('penambahansouvenir.index')
                ->with('success', 'Data distribusi berhasil disimpan dan notifikasi terkirim.');

        } catch (\Exception $e) {
            // 7. Rollback jika ada error
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

        $souvenirs = Souvenir::where('stok', '>', 0)
                        ->orWhere('id', $penambahan->id_souvenir)
                        ->get();

        $startDate = now()->subWeek()->startOfDay();
        $endDate   = now()->addWeeks(3)->endOfDay();

        $rkms = RKM::with('materi')
                    ->whereBetween('tanggal_awal', [$startDate, $endDate])
                    ->orWhere('id', $penambahan->id_rkm)
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
        // 1. Pastikan Relasi Terload (RKM & Souvenir)
        // Kita perlu load relasi 'souvenir' untuk mengambil namanya
        $penambahan->load(['souvenir', 'rkm.materi']);

        $rkm = $penambahan->rkm;
        $namaMateri = $rkm->materi->nama_materi ?? $rkm->nama_program ?? 'RKM #' . $rkm->id;

        // 2. Susun Detail Barang
        // Karena update biasanya per satu baris (row), kita bungkus dalam array
        $listBarang = [
            [
                'nama_souvenir' => $penambahan->souvenir->nama_souvenir ?? 'Item',
                'qty' => $penambahan->qty
            ]
        ];

        // 3. Susun Payload Data (Harus Lengkap Sesuai Struktur Baru)
        $dataNotif = [
            'id_karyawan'       => auth()->user()->karyawan->id,
            'tipe'              => 'Souvenir',
            'tanggal_pengajuan' => $penambahan->tanggal,
            'nama_rkm'          => $namaMateri,
            'rkm_start'         => $rkm->tanggal_awal,
            'rkm_end'           => $rkm->tanggal_akhir,

            // Data Tambahan (PENTING: Agar tidak error di View)
            'penerima_nama'     => $penambahan->nama,
            'penerima_jabatan'  => $penambahan->jabatan,
            'detail_barang'     => $listBarang
        ];

        // Ubah Judul Type agar user tahu ini hasil edit
        $type = 'Update Distribusi Souvenir';
        $path = '/penambahansouvenir';

        // 4. Kirim Notifikasi (Gunakan Loop seperti di method store)

        // A. Kirim ke GM
        $usersGM = User::whereHas('karyawan', function($q) {
            $q->where('jabatan', 'GM');
        })->get();

        foreach ($usersGM as $gm) {
            $gm->notify(new PenambahanSouvenirNotification($dataNotif, $path, $type, $gm->id));
        }

        // B. Kirim ke Diri Sendiri
        $userSelf = auth()->user();
        if ($userSelf) {
            $userSelf->notify(new PenambahanSouvenirNotification($dataNotif, $path, $type, $userSelf->id));
        }
    }

}
