<?php

namespace App\Http\Controllers\Crm;
use App\Http\Controllers\Controller;

use App\Models\CatatanSales;
use Illuminate\Http\Request;

class CatatanSalesController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_aktivitas' => 'required|integer',
            'catatan'         => 'required|string',
        ]);

        // hanya untuk test function di postman, setelah selesai tolong diubah -> auth()->user()->id_sales
        $validated['id_sales'] = $request->input('id_sales', auth()->user()->id_sales ?? null);

        $contact = CatatanSales::create($validated);

        return back()->with([
            'message' => 'Catatan berhasil disimpan.',
        ]);
    }

    public function delete($id)
    {
        $catatan = CatatanSales::where('id', $id)->first();
        $catatan->delete();

        return back()->with([
            'message' => 'Catatan berhasil dihapus.',
        ]);
    }

    public function update($id, Request $request)
    {
        $catatan = CatatanSales::where('id', $id)->first();

        $catatan->catatan = $request->catatan;

        $catatan->update();

        return back()->with([
            'message' => 'Catatan berhasil di perbarui.',
        ]);
    }
}
