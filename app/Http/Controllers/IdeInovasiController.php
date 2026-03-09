<?php

namespace App\Http\Controllers;

use App\Models\IdeInovasi;
use App\Models\User;
use App\Notifications\IdeInovasiNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class IdeInovasiController extends Controller
{
    public function index()
    {
        $ideInovasis = IdeInovasi::with('karyawan')->get();
        return view('ide_inovasi.index', compact('ideInovasis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'       => 'required|string|max:255',
            'deskripsi'   => 'required|string',
        ]);

        $data = $request->all();
        $data['id_karyawan'] = auth()->user()->id;

        // Menyimpan data Ide Inovasi
        $ideInovasi = IdeInovasi::create($data);

        // Mengambil data user ITSM
        $itsmUsers = User::whereHas('karyawan', function ($query) {
            $query->where('divisi', 'IT Service Management');
        })->get();

        // Konfigurasi notifikasi
        $path = route('ide-inovasi.index'); // atau '/ide-inovasi'
        $status = "Ide Inovasi Baru";

        // Melakukan perulangan untuk mengirim notifikasi secara individual
        foreach ($itsmUsers as $user) {
            $receiverId = $user->id;
            NotificationFacade::send($user, new IdeInovasiNotification($ideInovasi, $path, $status, $receiverId));
        }

        return redirect()->route('ide-inovasi.index')
                         ->with('success', 'Data ide inovasi berhasil ditambahkan.');
    }

    public function update(Request $request, IdeInovasi $ideInovasi)
    {
        $request->validate([
            'judul'       => 'required|string|max:255',
            'deskripsi'   => 'required|string',
        ]);

        $ideInovasi->update($request->only(['judul', 'deskripsi']));

        return redirect()->route('ide-inovasi.index')
                         ->with('success', 'Data ide inovasi berhasil diperbarui.');
    }

    public function destroy(IdeInovasi $ideInovasi)
    {
        $ideInovasi->delete();

        return redirect()->route('ide-inovasi.index')
                         ->with('success', 'Data ide inovasi berhasil dihapus.');
    }
}
