<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Perusahaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AktivitasController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM'];

        if ($user->jabatan === 'Sales') {
            $idSales = $user->id_sales;
            $data = Aktivitas::where('id_sales', $idSales)->get();
            $contact = Perusahaan::where('sales_key', $idSales)->get();
        } elseif (in_array($user->jabatan, $allowedJabatan)) {
            $data = Aktivitas::all();
            $contact = Perusahaan::all();
        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return view('crm.aktivitas.index', compact('data', 'contact'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_contact' => 'required|integer',
            'id_peluang' => 'required|integer',
            'aktivitas' => 'required|in:Call,Email,Visit',
            'subject' => 'required|string',
            'deskripsi' => 'required|string',
            'waktu_aktivitas' => 'required|date',
        ]);

        // hanya untuk test function di postman, setelah selesai tolong diubah -> auth()->user()->id_sales
        $validated['id_sales'] = $request->input('id_sales', auth()->user()->id_sales ?? null);

        $aktivitas = Aktivitas::create($validated);

        return back()->with([
            'message' => 'Aktivitas berhasil direcord.',
            'data' => $aktivitas,
        ]);
    }

    public function storeNew(Request $request)
    {
        $validated = $request->validate([
            'id_contact' => 'required|integer',
            'aktivitas' => 'required|in:Call,Email,Visit',
            'subject' => 'required|string',
            'deskripsi' => 'nullable|string',
            'waktu_aktivitas' => 'required|date',
        ]);

        // hanya untuk test function di postman, setelah selesai tolong diubah -> auth()->user()->id_sales
        $validated['id_sales'] = $request->input('id_sales', auth()->user()->id_sales ?? null);

        $aktivitas = Aktivitas::create($validated);

        return back()->with([
            'message' => 'Aktivitas berhasil direcord.',
            'data' => $aktivitas,
        ]);
    }

    public function delete($id)
    {
        $aktivitas = Aktivitas::where('id', $id)->first();
        $aktivitas->delete();

        return back()->with([
            'message' => 'Aktivitas berhasil dihapus.',
        ]);
    }

    public function update($id, Request $request)
    {
        $aktivitas = Aktivitas::where('id', $id)->first();

        $aktivitas->aktivitas = $request->aktivitas;
        $aktivitas->subject = $request->subject;
        $aktivitas->deskripsi = $request->deskripsi;
        $aktivitas->waktu_aktivitas = $request->waktu_aktivitas;

        $aktivitas->update();

        return back()->with([
            'message' => 'Aktivitas berhasil di perbarui.',
        ]);
    }
}
