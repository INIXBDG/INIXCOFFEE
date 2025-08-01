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
        $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM', 'SPV Sales'];

        if ($user->jabatan === 'Sales') {
            $idSales = $user->id_sales;
            $data = Perusahaan::where('sales_key', $idSales)->get();
            $perusahaan = Perusahaan::where('sales_key', $user->id_sales)->get();
        } elseif (in_array($user->jabatan, $allowedJabatan)) {
            $data = Contact::all();
            $perusahaan = Perusahaan::all();
        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return view('crm.contact.index', compact('data', 'perusahaan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_perusahaan' => 'required|integer',
            'nama_lengkap'  => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'cp'        => 'nullable|string|max:20',
            'divisi'        => 'required|string|max:255',
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
        // Validasi input request
        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'kategori_perusahaan' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'lokasi' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'alamat' => 'nullable|string|max:500',
            'no_telp' => 'nullable|string|max:20',
            'cp' => 'nullable|string|max:100',
            'foto_npwp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        $contact = Perusahaan::findOrFail($id);

        // Update atribut dari data yang sudah tervalidasi
        $contact->nama_perusahaan = $validated['nama_perusahaan'];
        $contact->kategori_perusahaan = $validated['kategori_perusahaan'];
        $contact->email = $validated['email'];
        $contact->lokasi = $validated['lokasi'] ?? $contact->lokasi;
        $contact->status = $validated['status'];
        $contact->npwp = $validated['npwp'] ?? $contact->npwp;
        $contact->alamat = $validated['alamat'] ?? $contact->alamat;
        $contact->no_telp = $validated['no_telp'] ?? $contact->no_telp;
        $contact->cp = $validated['cp'] ?? $contact->cp;

        // Upload file jika ada unggahan baru
        if ($request->hasFile('foto_npwp')) {
            $file = $request->file('foto_npwp');
            $extension = $file->getClientOriginalExtension();
            $filename = $validated['nama_perusahaan'] . '_npwp.' . $extension;
            $file->storeAs('public/npwp', $filename);

            // Simpan path file ke kolom foto_npwp (pastikan ada kolom ini)
            $contact->foto_npwp = 'npwp/' . $filename;
        }

        $contact->update;

        return back()->with([
            'message' => 'Kontak berhasil diperbarui.',
        ]);
    }

}
