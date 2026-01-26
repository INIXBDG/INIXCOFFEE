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

        $sertifikasiQuery = Sertifikasi::with(['user.karyawan', 'approver']);
        $pelatihanQuery = Pelatihan::with(['user.karyawan', 'approver']);

        if (!$isManager) {
            $sertifikasiQuery->where('user_id', $user->id);
            $pelatihanQuery->where('user_id', $user->id);
        }
        $sertifikasis = $sertifikasiQuery->latest()->get();
        $pelatihans = $pelatihanQuery->latest()->get();

        return view('development.index', compact('sertifikasis', 'pelatihans'));
    }

    public function storeSertifikasi(Request $request)
    {
        $request->validate([
            'nama_sertifikat'        => 'required|string|max:255',
            'penyedia'               => 'required|string|max:255',
            'tanggal_ujian'          => 'required|date',
            'tanggal_berlaku_dari'   => 'required|date',
            'tanggal_berlaku_sampai' => 'nullable|date|after_or_equal:tanggal_berlaku_dari',
            'harga'                  => 'required|numeric|min:0',
            'vendor'                 => 'required|string|max:255',
        ]);

        $userId = Auth::id();

        // Cari sertifikat existing dengan kriteria sama
        $existingCert = Sertifikasi::where('user_id', $userId)
            ->where('nama_sertifikat', $request->nama_sertifikat)
            ->where('penyedia', $request->penyedia)
            ->where('vendor', $request->vendor)
            ->orderByDesc('tanggal_berlaku_sampai')
            ->first();

        if ($existingCert) {
            // Sertifikat lama berlaku seumur hidup
            if (is_null($existingCert->tanggal_berlaku_sampai)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Gagal: Sertifikasi ini sudah terdaftar dan statusnya berlaku seumur hidup.');
            }

            // Jika tanggal ujian baru <= tanggal berakhir lama, berarti masih aktif
            if ($request->tanggal_ujian <= $existingCert->tanggal_berlaku_sampai) {
                $formattedDate = Carbon::parse($existingCert->tanggal_berlaku_sampai)->translatedFormat('d F Y');
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Gagal: Sertifikasi serupa masih berlaku hingga {$formattedDate}. Anda baru bisa menginput data jika tanggal ujian melewati tanggal tersebut.");
            }
        }

        // 3. Proses Penyimpanan (Jika lolos validasi di atas)
        try {
            DB::beginTransaction();

            $sertifikasi = Sertifikasi::create([
                'user_id'                => Auth::id(),
                'nama_sertifikat'        => $request->nama_sertifikat,
                'penyedia'               => $request->penyedia,
                'tanggal_ujian'          => $request->tanggal_ujian,
                'tanggal_berlaku_dari'   => $request->tanggal_berlaku_dari,
                'tanggal_berlaku_sampai' => $request->tanggal_berlaku_sampai,
                'harga'                  => $request->harga,
                'vendor'                 => $request->vendor,
                'status_approval'        => 'pending',
            ]);

            // Kirim Notifikasi ke Education Manager
            $managers = karyawan::where('jabatan', 'Education Manager')->get();
            foreach ($managers as $manager) {
                $userManager = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $manager->kode_karyawan))->first();

                if ($userManager) {
                    $dataNotif = [
                        'id_user'           => Auth::id(),
                        'tipe_kategori'     => 'Sertifikasi',
                        'nama_item'         => $sertifikasi->nama_sertifikat,
                        'tanggal_pengajuan' => now(),

                        // Data Detail
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

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan sertifikasi: ' . $e->getMessage())->withInput();
        }

        return redirect()->back()->with('success', 'Sertifikasi berhasil disimpan.');
    }

    /**
     * Menyimpan Pelatihan baru.
     */
    public function storePelatihan(Request $request)
    {
        $request->validate([
            'nama_pelatihan'  => 'required|string|max:255',
            'penyedia'        => 'required|string|max:255',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'nullable|string',
            'harga'           => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $pelatihan = Pelatihan::create([
                'user_id'           => Auth::id(),
                'nama_pelatihan'    => $request->nama_pelatihan,
                'penyedia'          => $request->penyedia,
                'tanggal_mulai'     => $request->tanggal_mulai,
                'tanggal_selesai'   => $request->tanggal_selesai,
                'keterangan'        => $request->keterangan,
                'harga'             => $request->harga,
                'status_approval'   => 'pending',
            ]);

            // Kirim Notifikasi ke Manager
            $managers = karyawan::where('jabatan', 'Education Manager')->get();
            foreach ($managers as $manager) {
                $userManager = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $manager->kode_karyawan))->first();

                if ($userManager) {
                    $dataNotif = [
                        'id_user'           => Auth::id(),
                        'tipe_kategori'     => 'Pelatihan',
                        'nama_item'         => $pelatihan->nama_pelatihan,
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

        return redirect()->back()->with('success', 'Pelatihan berhasil disimpan.');
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

            $sertifikasi->update([
                'status_approval' => $status,
                'approved_by'     => Auth::id(),
                'approved_at'     => now(),
            ]);

            // Kirim Notifikasi Balik ke Pengaju
            $pengaju = $sertifikasi->user;
            if ($pengaju) {
                $notifType = ($status == 'approved') ? 'Pengembangan Diri Disetujui' : 'Pengembangan Diri Ditolak';
                $pesanStatus = ($status == 'approved') ? 'Disetujui' : 'Ditolak';

                $dataNotif = [
                    'tipe_kategori'     => 'Sertifikasi',
                    'nama_item'         => $sertifikasi->nama_sertifikat,
                    'status'            => 'Status diubah menjadi: ' . $pesanStatus,
                    'tanggal_pengajuan' => now(),
                    'tanggal_ujian'     => $sertifikasi->tanggal_ujian,
                    'berlaku_dari'      => $sertifikasi->tanggal_berlaku_dari,
                    'berlaku_sampai'    => $sertifikasi->tanggal_berlaku_sampai,
                    'harga'             => $sertifikasi->harga,
                ];

                $path = '/development';
                $receiverId = $pengaju->id;

                NotificationFacade::send($pengaju, new DevelopmentNotification($dataNotif, $path, $notifType, $receiverId));
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

            $pelatihan->update([
                'status_approval' => $status,
                'approved_by'     => Auth::id(),
                'approved_at'     => now(),
            ]);

            $pengaju = $pelatihan->user;
            if ($pengaju) {
                $notifType = ($status == 'approved') ? 'Pengembangan Diri Disetujui' : 'Pengembangan Diri Ditolak';
                $pesanStatus = ($status == 'approved') ? 'Disetujui' : 'Ditolak';

                $dataNotif = [
                    'tipe_kategori'     => 'Pelatihan',
                    'nama_item'         => $pelatihan->nama_pelatihan,
                    'status'            => 'Status diubah menjadi: ' . $pesanStatus,
                    'tanggal_pengajuan' => now(),
                    'tanggal_mulai'     => $pelatihan->tanggal_mulai,
                    'tanggal_selesai'   => $pelatihan->tanggal_selesai,
                    'harga'             => $pelatihan->harga,
                ];

                $path = '/development';
                $receiverId = $pengaju->id;

                NotificationFacade::send($pengaju, new DevelopmentNotification($dataNotif, $path, $notifType, $receiverId));
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack(); 
            return redirect()->back()->with('error', 'Gagal update status: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Status Pelatihan diperbarui.');
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
            'penyedia'        => 'required|string|max:255',
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

        if ($sertifikasi->status_approval === 'approved') {
            return redirect()->back()->with('error', 'Data yang sudah disetujui tidak dapat diedit.');
        }

        $request->validate([
            'nama_sertifikat'        => 'required|string|max:255',
            'penyedia'               => 'required|string|max:255',
            'tanggal_ujian'          => 'required|date',
            'tanggal_berlaku_dari'   => 'required|date',
            'tanggal_berlaku_sampai' => 'nullable|date|after_or_equal:tanggal_berlaku_dari',
            'harga'                  => 'required|numeric|min:0',
            'vendor'                 => 'required|string|max:255',
        ]);

        $sertifikasi->update($request->all());

        return redirect()->back()->with('success', 'Sertifikasi berhasil diperbarui.');
    }
}
