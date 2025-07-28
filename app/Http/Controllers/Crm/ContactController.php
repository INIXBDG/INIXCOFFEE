<?php

namespace App\Http\Controllers\Crm;
use App\Http\Controllers\Controller;

use App\Models\Contact;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM'];

        if ($user->jabatan === 'Sales') {
            $idSales = $user->id_sales;
            $data = Contact::where('id_sales', $idSales)->get();
        } elseif (in_array($user->jabatan, $allowedJabatan)) {
            $data = Contact::all();
        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $perusahaan = Perusahaan::all();
        return view('crm.contact.index', compact('data', 'perusahaan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_perusahaan' => 'required|integer',
            'nama_lengkap'  => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'no_tlp'        => 'nullable|string|max:20',
            'tim'        => 'required|string|max:255',
            'posisi'        => 'required|string|max:255',
        ]);

        // hanya untuk test function di postman, setelah selesai tolong diubah -> auth()->user()->id_sales
        $validated['id_sales'] = $request->input('id_sales', auth()->user()->id_sales ?? null);

        $contact = Contact::create($validated);

        return back()->with([
            'message' => 'Kontak berhasil disimpan.',
            'data' => $contact,
        ]);
    }

    public function delete($id)
    {
        $contact = Contact::where('id', $id)->first();
        $contact->delete();

        return back()->with([
            'message' => 'Kontak berhasil dihapus.',
        ]);
    }

    public function update($id, Request $request)
    {
        $contact = Contact::where('id', $id)->first();

        $contact->nama_lengkap = $request->nama_lengkap;
        $contact->email = $request->email;
        $contact->no_tlp = $request->no_tlp;

        $contact->update();

        return back()->with([
            'message' => 'Kontak berhasil di perbarui.',
        ]);
    }
}
