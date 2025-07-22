<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use Illuminate\Http\Request;

class AktivitasController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_contact' => 'required|integer',
            'id_peluang' => 'required|integer',
            'aktivitas' => 'required|in:Panggilan,Email,Meeting,Catatan,Task',
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
