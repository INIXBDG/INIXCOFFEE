<?php

namespace App\Http\Controllers\office;

use App\Exports\ModulPesertaExport;
use App\Http\Controllers\Controller;
use App\Models\karyawan;
use App\Models\Materi;
use App\Models\Modul;
use App\Models\NomorModul;
use App\Models\Perusahaan;
use App\Models\PesertaModul;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ModulController extends Controller
{

    public function indexNomor()
    {
        $nomor = NomorModul::all();
        $romanMonths = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];

        $monthNumber   = now()->format('n');
        $romanMonth    = $romanMonths[$monthNumber];
        $yearTwoDigit  = now()->format('y');
        $yearFull      = now()->year;

        // Ambil nomor terakhir berdasarkan tahun
        $last = NomorModul::whereYear('created_at', $yearFull)
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            // Pecah string: M/BDG/000/XII/25 → Ambil index ke-2 (002)
            $parts = explode('/', $last->no_modul);
            $lastCode = intval($parts[2]); // 002 → 2
            $newCode = str_pad($lastCode + 1, 3, '0', STR_PAD_LEFT);
        } else {
            // Jika tahun baru → mulai dari 000
            $newCode = "000";
        }

        $noModul = "M/BDG/$newCode/$romanMonth/$yearTwoDigit";

        return view('office.nomorModul.index', compact('noModul', 'nomor'));
    }


    public function indexModul($id)
    {
        $nomor = NomorModul::findOrFail($id);
        $modul = Modul::where('no_modul', $id)->get();
        $materi = Materi::all();
        $perusahaan = Perusahaan::all();
        $peserta = PesertaModul::with('perusahaan')->where('no_modul', $id)->get();

        $modul = $modul->map(function ($item) {
            $materi_asli = Materi::where('nama_materi', $item->nama_materi)->first();
            $item->materi_id = $materi_asli ? $materi_asli->id : null;
            return $item;
        });

        return view('office.modul.index', compact('nomor', 'modul', 'materi', 'perusahaan', 'peserta'));
    }

    public function storeModul(Request $request)
    {
        $request->validate([
            'no_modul'       => 'required',
            'materi_id'      => 'required',
            'awal_training'  => 'required|date',
            'akhir_training' => 'required|date|after_or_equal:awal_training',
            'jumlah'         => 'required|integer|min:1',
            'harga_satuan'   => 'required|numeric|min:0',
            'note'           => 'nullable',
        ]);

        $total   = $request->jumlah * $request->harga_satuan;
        $materi  = Materi::findOrFail($request->materi_id);
        $nomor   = NomorModul::findOrFail($request->no_modul);

        if ($nomor->type == 'Authorize') {

            $modul = Modul::where('no_modul', $request->no_modul)->count();

            if ($modul >= 1) {
                return back()->with('error', 'Modul untuk Authorize hanya boleh 1. Data sudah tersedia.');
            }
        }

        Modul::create([
            'no_modul'      => $request->no_modul,
            'kode_materi'   => $materi->kode_materi,
            'nama_materi'   => $materi->nama_materi,
            'awal_training' => $request->awal_training,
            'akhir_training' => $request->akhir_training,
            'jumlah'        => $request->jumlah,
            'harga_satuan'  => $request->harga_satuan,
            'total'         => $total,
            'note'          => $request->note,
        ]);

        return redirect()
            ->route('office.modul.detail', ['id' => $request->no_modul])
            ->with('success', 'Modul berhasil ditambahkan');
    }


    public function updateModul(Request $request, $id)
    {
        $request->validate([
            'materi_id'         => 'required',
            'awal_training'     => 'required|date',
            'akhir_training'    => 'required|date|after_or_equal:awal_training',
            'jumlah'            => 'required|integer|min:1',
            'harga_satuan'      => 'required|numeric|min:0',
            'note'              => 'nullable',
        ]);

        $modul = Modul::findOrFail($id);
        $materi = Materi::findOrFail($request->materi_id);

        $total = $request->jumlah * $request->harga_satuan;

        $modul->update([
            'materi_id'        => $materi->id,
            'kode_materi'      => $materi->kode_materi,
            'nama_materi'      => $materi->nama_materi,
            'awal_training'    => $request->awal_training,
            'akhir_training'   => $request->akhir_training,
            'jumlah'           => $request->jumlah,
            'harga_satuan'     => $request->harga_satuan,
            'total'            => $total,
            'note'             => $request->note,
        ]);

        return redirect()->route('office.modul.detail', ['id' => $request->nomodul])->with('success', 'Modul berhasil diupdate');
    }

    public function deleteModul($id)
    {
        Modul::where('id', $id)->delete();
        PesertaModul::where('modul', $id)->delete();
        return back()->with('success', 'Modul dan data" terkait berhasil dihapus');
    }

    public function storeNomor(Request $request)
    {
        $request->validate([
            'no_modul'  => 'required',
            'type'      => 'in:Regular,Authorize',
        ]);

        if (NomorModul::where('no_modul', $request->no_modul)->exists()) {
            return back()->with('error', 'Nomor modul sudah ada, silahkan gunakan nomor yang lain');
        }

        NomorModul::create([
            'no_modul' => $request->no_modul,
            'type' => $request->type,
        ]);

        return redirect()
            ->route('office.modul.index')
            ->with('success', 'Nomor Modul berhasil ditambahkan');
    }


    public function updateNomor(Request $request, $id)
    {
        $request->validate([
            'no_modul'  => 'required',
            'type'      => 'in:Regular,Authorize',
        ]);

        $nomor = NomorModul::findOrFail($id);

        $nomor->update([
            'no_modul' => $request->no_modul,
            'type' => $request->type,
        ]);

        if ($request->type === 'Regular') {
            $peserta = PesertaModul::where('no_modul', $id)->get();
            if ($peserta->count() > 0) {
                PesertaModul::where('no_modul', $id)->delete();
            }
        }

        return redirect()->route('office.modul.index')->with('success', 'Nomor Modul berhasil diupdate');
    }


    public function deleteNomor($id)
    {
        $nomor = NomorModul::findOrFail($id);

        Modul::where('no_modul', $nomor->no_modul)->delete();
        PesertaModul::where('no_modul', $id)->delete();

        $nomor->delete();

        return redirect()
            ->route('office.modul.index')
            ->with('success', 'Nomor Modul beserta semua modul terkait berhasil dihapus');
    }

    public function storePeserta(Request $request)
    {
        $request->validate([
            'no_modul'       => 'required',
            'modul'          => 'required',
            'nama_peserta'   => 'required|string|max:255',
            'email_peserta'  => 'nullable|email|max:255',
            'perusahaan_id'  => 'nullable',
            'awal_training'  => 'required|date',
            'akhir_training' => 'required|date|after_or_equal:awal_training',
        ]);

        $materi = Materi::where('id', $request->modul)->first();
        $modul = NomorModul::where('id', $request->no_modul)->first();
        $jumlah = Modul::where('no_modul', $modul->id)->first();
        if (PesertaModul::where('no_modul', $modul->id)->count() >= $jumlah->jumlah) {
            return back()->with('error', 'Peserta sudah mencapat batas, silahkan periksa kembali.');
        }

        if (!$materi) {
            return back()->with('error', 'Materi tidak ditemukan. Silakan pilih modul yang valid.');
        }

        PesertaModul::create([
            'no_modul'       => $request->no_modul,
            'modul'          => $materi->id,
            'nama_peserta'   => $request->nama_peserta,
            'email'          => $request->email_peserta,
            'perusahaan_id'  => $request->perusahaan_id,
            'awal_training'  => $request->awal_training,
            'akhir_training' => $request->akhir_training,
        ]);

        return redirect()->route('office.modul.detail', $request->no_modul)->with('success', 'Peserta berhasil ditambahkan');
    }

    public function updatePeserta(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nama_peserta'   => 'required|string|max:255',
                'email_peserta'  => 'nullable|email|max:255',
                'perusahaan_id'  => 'nullable',
                'awal_training'  => 'required|date',
                'akhir_training' => 'required|date|after_or_equal:awal_training',
            ]);
            Log::info('UpdatePeserta: validasi berhasil', $validated);
        } catch (\Exception $e) {
            Log::error('UpdatePeserta: validasi gagal', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        $peserta = PesertaModul::find($id);
        if (!$peserta) {
            Log::error('UpdatePeserta: peserta tidak ditemukan', [
                'peserta_id' => $request->peserta_id
            ]);
            return back()->with('error', 'Peserta tidak ditemukan');
        }

        Log::info('UpdatePeserta: peserta ditemukan', [
            'peserta' => $peserta
        ]);

        $updateData = [
            'nama_peserta'   => $request->nama_peserta,
            'email'          => $request->email_peserta,
            'perusahaan_id'  => $request->perusahaan_id,
            'awal_training'  => $request->awal_training,
            'akhir_training' => $request->akhir_training,
        ];

        Log::info('UpdatePeserta: data yang akan diupdate', $updateData);

        $peserta->update($updateData);

        Log::info('UpdatePeserta: update berhasil', [
            'peserta_id' => $peserta->id
        ]);

        return back()->with('success', 'Data peserta berhasil diperbarui');
    }

    public function deletePeserta(Request $request, $id)
    {
        PesertaModul::findOrFail($id)->delete();
        return back()->with('success', 'Data peserta berhasil dihapus');
    }

    public function pdfModul(Request $request, $id)
    {
        NomorModul::where('id', $id)->update([
            'note_modul' => $request->note,
        ]);

        $no = NomorModul::findOrFail($id);
        $modul = Modul::with('nomorModul')->where('no_modul', $id)->get();

        $ttd = karyawan::where('status_aktif', '1')->where('jabatan', 'Admin Holding')->first();

        $grandTotal = $modul->sum('total');
        Log::info('Data yg terikirim', [$modul, $no]);

        $pdf = Pdf::loadView('office.modul.pdf', compact('modul', 'grandTotal', 'no', 'ttd'));

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('PO_Materi' . $id . '.pdf');
    }

    public function pdfPeserta(Request $request, $id)
    {
        NomorModul::where('id', $id)->update([
            'note_peserta' => $request->note,
        ]);

        $no = NomorModul::findOrFail($id);

        $peserta = PesertaModul::with(['perusahaan', 'dataModul'])->where('no_modul', $id)->get();

        $ttd = karyawan::where('status_aktif', '1')->where('jabatan', 'Admin Holding')->first();

        $pdf = Pdf::loadView('office.modul.pdfPeserta', compact('no', 'peserta', 'ttd'));

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('Peserta_' . $id . '.pdf');
    }

    public function excelPeserta(Request $request, $id)
    {
        // 1. Update Note (Sama seperti PDF)
        // Jika request datang dari modal yang sama, logic ini tetap jalan
        if ($request->has('note')) {
            NomorModul::where('id', $id)->update([
                'note_peserta' => $request->note,
            ]);
        }

        // 2. Ambil Data (Sama seperti PDF)
        $no = NomorModul::findOrFail($id);
        $peserta = PesertaModul::with(['perusahaan', 'dataModul'])->where('no_modul', $id)->get();
        $ttd = karyawan::where('status_aktif', '1')->where('jabatan', 'Admin Holding')->first();

        // 3. Return Download Excel
        // Parameter constructor dikirim ke Class Export
        return Excel::download(new ModulPesertaExport($no, $peserta, $ttd), 'Peserta_' . $id . '.xlsx');
    }
}
