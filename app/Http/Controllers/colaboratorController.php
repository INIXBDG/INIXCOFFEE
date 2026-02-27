<?php

namespace App\Http\Controllers;

use App\Models\colaborator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class colaboratorController extends Controller
{
    public function index()
    {
        // Memuat data colaborator beserta data perusahaan jika nama_partner = nama_perusahaan
        $colaborators = colaborator::with('perusahaan')->get();
        
        return view('colaborator.index', compact('colaborators'));
    }

    /**
     * Menyediakan data JSON untuk DataTables dengan filter Kuartal.
     */
    public function getData(Request $request)
    {
        $query = Colaborator::with('perusahaan');

        $quarter = $request->input('quarter'); 
        $year = $request->input('year'); 

        if ($quarter && $year) {
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;

            $query->whereYear('start_date', $year)
                  ->whereMonth('start_date', '>=', $startMonth)
                  ->whereMonth('start_date', '<=', $endMonth);
        } elseif ($year) {
            $query->whereYear('start_date', $year);
        }

        $colaborators = $query->get();

        return response()->json([
            'data' => $colaborators
        ]);
    }

    /**
     * Menampilkan formulir pembuatan data kolaborasi baru.
     */
    public function create()
    {
        return view('colaborator.create');
    }

    /**
     * Menampilkan formulir pembaruan data kolaborasi.
     */
    public function edit(Colaborator $colaborator)
    {
        return view('colaborator.edit', compact('colaborator'));
    }

    /**
     * Menyimpan data kolaborasi baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_partner'  => 'required|string|max:255',
            'title'         => 'required|string|max:255',
            'type'          => 'required|string|max:255',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'status'        => 'required|string|max:255',
            'desc'          => 'required|string',
            'document_mou'  => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($request->hasFile('document_mou')) {
            $validated['document_mou'] = $request->file('document_mou')->store('documents/mou', 'public');
        }

        Colaborator::create($validated);

        return redirect()->route('colaborator.index')
                         ->with('success', 'Data kolaborasi berhasil disimpan.');
    }

    /**
     * Memperbarui data kolaborasi.
     */
    public function update(Request $request, Colaborator $colaborator)
    {
        $validated = $request->validate([
            'nama_partner'  => 'required|string|max:255',
            'title'         => 'required|string|max:255',
            'type'          => 'required|string|max:255',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'status'        => 'required|string|max:255',
            'desc'          => 'required|string',
            'document_mou'  => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($request->hasFile('document_mou')) {
            if ($colaborator->document_mou) {
                Storage::disk('public')->delete($colaborator->document_mou);
            }
            $validated['document_mou'] = $request->file('document_mou')->store('documents/mou', 'public');
        }

        $colaborator->update($validated);

        return redirect()->route('colaborator.index')
                         ->with('success', 'Data kolaborasi berhasil diperbarui.');
    }
}
