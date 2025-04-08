<?php

namespace App\Http\Controllers;

use App\Models\notif;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

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
    public function store(Request $request): RedirectResponse
    {
        // dd($request->all());
        $this->validate($request, [
            // 'id_user' => 'nullable',
            'tipe_notifikasi' => 'nullable',
            'isi_notifikasi' => 'nullable',
        ]);

        notif::create([
            'id_user' => auth()->user()->username,
            'tipe_notifikasi' => $request->tipe_notifikasi,
            'isi_notifikasi' => $request->isi_notifikasi,
            'tanggal_awal' => $request->tanggal_awal,
            'tanggal_akhir' => $request->tanggal_akhir,
        ]);

        return redirect()->route('home')->with(['success' => 'Data Berhasil Disimpan!']);
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
