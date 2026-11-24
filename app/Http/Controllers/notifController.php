<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use App\Models\notif;
use App\Models\User;
use App\Notifications\GeneralAnnouncementNotification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

class notifController extends Controller
{
    public function index()
    {

        return view('notif.index');
    }

    /**
     * create
     *
     * @return View
     */
    public function create(): View
    {
        return view('notif.create');
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            // 'id_user' => 'nullable',
            'tipe_notifikasi' => 'nullable',
            'isi_notifikasi' => 'nullable',
        ]);
        
        if ($request->ajax()){
            $data = HariLibur::where('id', $request->id)->first();

            $announcement = notif::create([
                'id_user' => auth()->user()->username,
                'tipe_notifikasi' => 'Libur',
                'isi_notifikasi' => $request->isi_notifikasi,
                'tanggal_awal' => $request->tanggal_awal,
                'tanggal_akhir' => $request->tanggal_akhir,
            ]);

            $this->sendAnnouncementNotification($announcement);

            session()->flash('success_libur', 'Notifikasi Libur berhasil dibuat');
            return response()->json([
                'status' => 'success_libur',
                'message' => 'Notifikasi Libur berhasil dibuat'
            ]);
        } 

        $announcement = notif::create([
            'id_user' => auth()->user()->username,
            'tipe_notifikasi' => $request->tipe_notifikasi,
            'isi_notifikasi' => $request->isi_notifikasi,
            'tanggal_awal' => $request->tanggal_awal,
            'tanggal_akhir' => $request->tanggal_akhir,
        ]);

        $this->sendAnnouncementNotification($announcement);

        return redirect()->route('home')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    private function sendAnnouncementNotification(notif $announcement): void
    {
        $recipients = User::query()
            ->where('status_akun', '1')
            ->with('karyawan')
            ->get();

        Notification::send(
            $recipients,
            new GeneralAnnouncementNotification(
                'Pengumuman ' . $announcement->tipe_notifikasi,
                $announcement->isi_notifikasi,
                '/home',
                $announcement->tipe_notifikasi,
                auth()->user()?->username
            )
        );
    }

    //

    /**
     * show
     *
     * @param  mixed $id
     * @return View
     */
    public function show(string $id)
    {

        return view('notif.show');
    }

    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        //get post by ID
        $notif = notif::findOrFail($id);

        //render view with post
        return view('notif.edit', compact('notif'));
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->validate($request, [
            // 'id_user' => 'nullable',
            'tipe_notifikasi' => 'nullable',
            'isi_notifikasi' => 'nullable',
        ]);

        $post = notif::findOrFail($id);

            $post->update([
                'id_user' => auth()->user()->username,
                'tipe_notifikasi' => $request->tipe_notifikasi,
                'isi_notifikasi' => $request->isi_notifikasi,
                'tanggal_awal' => $request->tanggal_awal,
                'tanggal_akhir' => $request->tanggal_akhir,
            ]);

        return redirect()->route('home')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id): RedirectResponse
    {
        $post = notif::findOrFail($id);

        $post->delete();

        return redirect()->route('home')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}
