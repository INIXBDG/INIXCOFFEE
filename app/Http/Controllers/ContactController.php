<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_perusahaan' => 'required|integer',
            'nama_lengkap'  => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'no_tlp'        => 'nullable|string|max:20',
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
