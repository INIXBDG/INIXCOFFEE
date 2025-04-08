<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\cc;
use Illuminate\Support\Facades\Auth;


class creditcardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(): View
    {
        $data = cc::latest()->paginate(5);

        return view('creditcard.index', compact('data'));
    }
    public function getCC()
    {
        $data = cc::get();

        $jabatan = Auth::user()->jabatan;
        if ($jabatan == 'Sales'|| $jabatan == 'Adm Sales' || $jabatan == 'GM'|| $jabatan == 'SPV Sales'
        || $jabatan == 'Instruktur'|| $jabatan == 'Education Manager' || $jabatan == 'Office Manager' || $jabatan == 'Koordinator Office'
        || $jabatan == 'Customer Care' || $jabatan == 'Customer Service' || $jabatan == 'Admin Holding' || $jabatan == 'Finance & Accounting'
        || $jabatan == 'HRD' || $jabatan == 'Koordinator Office' || $jabatan == 'Direktur Utama' || $jabatan == 'Direktur') {
            return response()->json([
                'success' => true,
                'message' => 'List CC',
                'data' => $data,
            ]);
        }else{
            return response()->json([
                'success' => true,
                'message' => 'List CC',
                'data' => '',
            ]);
        }
    }

    /**
     * create
     *
     * @return View
     */
    public function create(): View
    {
        return view('creditcard.create');
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
            'nama_pemilik'     => 'required',
            'angka_terakhir'     => 'required',
            'bank'     => 'required',
            'tipe_kartu'     => 'required',
        ]);

        cc::create([
            'nama_pemilik'     => $request->nama_pemilik,
            'angka_terakhir'     => $request->angka_terakhir,
            'bank'     => $request->bank,
            'tipe_kartu'     => $request->tipe_kartu,
        ]);

        return redirect()->route('creditcard.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    /**
     * show
     *
     * @param  mixed $id
     * @return View
     */
    public function show(string $id): View
    {
        $post = cc::findOrFail($id);

        return view('creditcard.show', compact('post'));
    }

    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        $creditcard = cc::findOrFail($id);

        return view('creditcard.edit', compact('creditcard'));
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
            'nama_pemilik'     => 'required',
            'angka_terakhir'     => 'required',
            'bank'     => 'required',
            'tipe_kartu'     => 'required',
        ]);

        $post = cc::findOrFail($id);

            $post->update([
                'nama_pemilik'     => $request->nama_pemilik,
                'angka_terakhir'     => $request->angka_terakhir,
                'bank'     => $request->bank,
                'tipe_kartu'     => $request->tipe_kartu,
            ]);

        return redirect()->route('creditcard.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id): RedirectResponse
    {
        $post = cc::findOrFail($id);

        $post->delete();

        return redirect()->route('creditcard.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}
