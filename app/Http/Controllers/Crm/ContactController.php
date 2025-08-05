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
        // Validasi input sesuai field perusahaan
        $validated = $request->validate([
            'nama_perusahaan'      => 'required|string|max:255',
            'kategori_perusahaan'  => 'nullable|string|max:255',
            'lokasi'               => 'nullable|string|max:255',
            // 'sales_key'            => 'nullable|string|max:255',
            'status'               => 'nullable|string|max:255',
            'npwp'                 => 'nullable|string|max:255',
            'alamat'               => 'nullable|string|max:1000',
            'cp'                   => 'nullable|string|max:20',
            'no_telp'              => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:255',
            'foto_npwp'            => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        // Handle upload foto_npwp, jika ada
        if ($request->hasFile('foto_npwp')) {
            $file = $request->file('foto_npwp');
            $extension = $file->getClientOriginalExtension();
            $filename = $validated['nama_perusahaan'] . '_npwp.' . $extension;
            $file->storeAs('public/npwp', $filename);
            $validated['foto_npwp'] = $filename;
        }

        // Menambahkan id_sales dari input manual atau default dari user login
        $id_sales = $request->input('id_sales', auth()->user()->id_sales ?? null);

        $validated['sales_key'] = $id_sales;

        // Simpan data perusahaan
        $perusahaan = Perusahaan::create($validated + ['sales_key' => $id_sales]);

        return back()->with([
            'message' => 'Data perusahaan berhasil disimpan.',
            'data' => $perusahaan,
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

            // Simpan path file ke kolom foto_npwp
            $contact->foto_npwp = 'npwp/' . $filename;
        }

        // Simpan perubahan ke database
        $contact->save();

        return back()->with([
            'message' => 'Kontak berhasil diperbarui.',
        ]);
    }

}
