<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\comment;
use App\Models\karyawan;
use App\Models\RKM;
use App\Models\User;
use App\Models\Notification;
use App\Notifications\CommentNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'karyawan_key' => 'required|exists:karyawans,id',
            'content' => 'required|string|min:1|max:250',
            'materi_key' => 'required',
            'rkm_key' => 'required|exists:r_k_m_s,id',
        ]);
        // return $request->path;
        // Menyimpan komentar
        $comment = Comment::create($validatedData);

        // Mendapatkan data RKM
        $rkm = RKM::findOrFail($request->rkm_key);
        $Offman = karyawan::where('jabatan', 'Office Manager')->first();
        $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();
        $Eduman = karyawan::where('jabatan', 'Education Manager')->first();
        $SPVSales = karyawan::where('jabatan', 'SPV Sales')->first();
        $GM = karyawan::where('jabatan', 'GM')->first();
        // Ambil koleksi karyawan (multiple), pastikan mereka berupa Laravel Collection
		$CS = collect(karyawan::where('jabatan', 'Customer Care')->latest()->get());
		$AH = collect(karyawan::where('jabatan', 'Admin Holding')->latest()->get());

		// Ekstrak kode_karyawan dari setiap model
		$cs_codes = $CS->pluck('kode_karyawan')->filter()->all(); // array string
		$ah_codes = $AH->pluck('kode_karyawan')->filter()->all(); // array string
		// return $cs_codes;  
        // Mengambil pengguna yang terlibat
        $users = array_map(function ($user) {
            return $user === '-' ? null : $user;
        }, [
            $rkm->sales_key,
            $rkm->instruktur_key,
            $rkm->instruktur_key2,
            $rkm->asisten_key,
            $Eduman->kode_karyawan,
            $Offman->kode_karyawan,
            $kooroff->kode_karyawan,
            $SPVSales->kode_karyawan,
            $GM->kode_karyawan,
            $cs_codes, // ✅ semua kode CS
			$ah_codes, // ✅ semua kode AH
        ]);

        // Pastikan $users adalah array datar
		$usersFlat = collect($users)
			->flatten() // 🔄 Flatten nested arrays
			->filter()   // 🧹 Hapus nilai kosong/null
			->values()   // 🧼 Reset index
			->all();     // 📥 Konversi ke PHP array biasa

		// Sekarang gunakan di query
		$users = User::whereHas('karyawan', function ($query) use ($usersFlat) {
			$query->whereIn('kode_karyawan', $usersFlat);
		})->get();

        // Mengatur URL halaman untuk notifikasi
        $url = route('rkm.show', ['rkm' => $rkm->id]);
        $path = $request->path;
        // return $path;
        // Mengirim notifikasi ke semua pengguna yang terlibat
        foreach ($users as $user) {
            NotificationFacade::send($user, new CommentNotification($comment, $url, $path));
        }

        return redirect()->back()->with('success', 'Komentar berhasil disimpan');
    }

    public function markAsRead($notificationId)
    {
        // dd($notificationId);
        // Temukan notifikasi berdasarkan ID
        $notification = \App\Models\Notification::findOrFail($notificationId);

        // Cek apakah notifikasi ada
        if ($notification) {
            $notification->update(['read_at' => now()]);
        }

        return redirect()->back();
    }

    public function markAllAsRead()
    {
        // Ambil semua notifikasi yang belum dibaca, kecuali yang bertipe OutstandingNotification dan BayarExamNotification
        $notifications = auth()->user()->unreadNotifications->filter(function ($notification) {
            return $notification->type !== "App\\Notifications\\OutstandingNotification"
                && $notification->type !== "App\\Notifications\\BayarExamNotification";
        });

        // Tandai semua notifikasi yang tersisa sebagai dibaca dengan timestamp sekarang
        $notifications->each(function ($notification) {
            $notification->update(['read_at' => now()]);
        });

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }




    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'karyawan_key' => 'required|exists:karyawans,id',
            'content' => 'required',
            'rkm_key' => 'required|exists:rkms,id',
        ]);

        $comment = Comment::findOrFail($id);
        $comment->update($validatedData);

        return redirect()->back()->with('success', 'Komentar berhasil diperbarui');
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return redirect()->back()->with('success', 'Komentar berhasil dihapus');
    }
}
