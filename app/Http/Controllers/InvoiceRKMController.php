<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\RKM;
use App\Models\Perusahaan; // Asumsi ada model Perusahaan
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class InvoiceRKMController extends Controller
{
    
    /**
     * Menampilkan halaman indeks dengan dua tabel.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Ambil semua ID RKM yang sudah ada di tabel invoices
        $existingRKMs = Invoice::pluck('id_rkm')->toArray();
        
        // Data untuk tabel 'Belum di-Invoice'
        $notInvoicedRkms = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan'])
            ->whereNotIn('id', $existingRKMs)
            ->orderBy('tanggal_awal', 'desc')
            ->get();

        // Data untuk tabel 'Sudah di-Invoice'
        $invoicedRkms = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan', 'invoice'])
            ->whereIn('id', $existingRKMs)
            ->orderBy('tanggal_awal', 'desc')
            ->get();

        return view('invoice.index', compact('notInvoicedRkms', 'invoicedRkms'));
    }
        

    /**
     * Menampilkan form untuk membuat invoice baru dari RKM tertentu.
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
public function create(string $id): View
{
    $rkm = RKM::with('perusahaan', 'materi')->findOrFail($id);
    return view('invoice.create', compact('rkm'));
}

    /**
     * Menyimpan invoice baru ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'invoice_number' => 'required|string|max:255|unique:invoices',
            'tanggal_invoice' => 'required|date',
            'id_rkm' => 'required|exists:r_k_m_s,id',
            'amount' => 'required|numeric',
        ]);

        Invoice::create($request->all());

        return redirect()->route('invoice.index')->with(['success' => 'Invoice berhasil dibuat!']);
    }

    /**
     * Menampilkan detail satu invoice.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\View\View
     */
public function show(string $id): View
{
    // Mengambil data Invoice dan memuat relasi RKM, Perusahaan, dan Materi
    $invoice = Invoice::with(['rkm.perusahaan', 'rkm.materi'])->findOrFail($id);
    
    return view('invoice.show', compact('invoice'));
}

    /**
     * Menampilkan form untuk mengedit invoice.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\View\View
     */
    public function edit(Invoice $invoice): View
    {
        $invoice->load('rkm.perusahaan', 'rkm.materi');
        return view('invoice.edit', compact('invoice'));
    }

    /**
     * Memperbarui invoice yang sudah ada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $request->validate([
            'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number,' . $invoice->id,
            'tanggal_invoice' => 'required|date',
            'id_rkm' => 'required|exists:rkm,id',
            'amount' => 'required|numeric',
        ]);

        $invoice->update($request->all());

        return redirect()->route('invoice.show', $invoice->id)->with('success', 'Invoice berhasil diperbarui!');
    }

    /**
     * Menghapus invoice dari database.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();
        return redirect()->route('invoice.index')->with('success', 'Invoice berhasil dihapus!');
    }
}