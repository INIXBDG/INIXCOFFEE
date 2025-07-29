<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;

use App\Models\Aktivitas;
use App\Models\Contact;
use App\Models\Materi;
use App\Models\Peluang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeluangController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM'];
        $materi = Materi::all();

        if ($user->jabatan === 'Sales') {
            $idSales = $user->id_sales;
            $data = Peluang::where('id_sales', $idSales)->get();
            $contact = Contact::where('id_sales', $idSales)->get();
        } elseif (in_array($user->jabatan, $allowedJabatan)) {
            $data = Peluang::all();
            $contact = Contact::all();
        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return view('crm.peluang.index', compact('data', 'contact', 'materi'));
    }

    public function detail($id)
    {
        $aktivitas = Aktivitas::where('id_peluang', $id)->get();
        $peluang = Peluang::where('id', $id)->first();
        return view('crm.peluang.detail', compact('peluang', 'aktivitas'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_contact' => 'required|integer',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jumlah' => 'nullable|numeric|min:0',
            'tahap' => 'nullable|in:hitam,biru,merah',
            'tanggal_tutup_diharapkan' => 'nullable|date',
        ]);

        // hanya untuk test function di postman, setelah selesai tolong diubah -> auth()->user()->id_sales
        $validated['id_sales'] = $request->input('id_sales', auth()->user()->id_sales ?? null);

        $peluang = Peluang::create($validated);

        return back()->with([
            'message' => 'Peluang berhasil dibuat.',
            'data' => $peluang,
        ]);
    }

    public function delete($id)
    {
        $peluang = Peluang::where('id', $id)->first();
        $peluang->delete();

        return back()->with([
            'message' => 'Peluang berhasil dihapus.',
        ]);
    }

    public function edit($id, Request $request)
    {
        $peluang = Peluang::where('id', $id)->first();

        $peluang->judul = $request->judul;
        $peluang->deskripsi = $request->deskripsi;

        $peluang->update();
        return back()->with([
            'message' => 'Pelaung berhasil di perbarui.',
        ]);
    }

    public function updateTahap($id, Request $request)
    {
        $peluang = Peluang::where('id', $id)->first();

        $peluang->tahap = $request->tahap;

        if ($request->has('close_win')) {
            $peluang->close_win = $request->close_win;
        }

        if ($request->has('close_lost')) {
            $peluang->close_lost = $request->close_lost;
        }

        $peluang->update();

        return back()->with([
            'message' => 'Tahap berhasil di perbarui.',
        ]);
    }

    public function ringkasanPeluang(Request $request)
    {
        $tahunDipilih = $request->query('tahun', now()->year);

        $dataRingkasan = Peluang::where('tahap', 'merah')
            ->whereYear('tanggal_tutup_diharapkan', $tahunDipilih)
            ->select(
                'id_sales',
                DB::raw('CASE
                WHEN MONTH(tanggal_tutup_diharapkan) BETWEEN 1 AND 3 THEN "Q1"
                WHEN MONTH(tanggal_tutup_diharapkan) BETWEEN 4 AND 6 THEN "Q2"
                WHEN MONTH(tanggal_tutup_diharapkan) BETWEEN 7 AND 9 THEN "Q3"
                WHEN MONTH(tanggal_tutup_diharapkan) BETWEEN 10 AND 12 THEN "Q4"
            END as kuartal'),
                DB::raw('SUM(close_win) as total_jumlah')
            )
            ->groupBy('id_sales', 'kuartal')
            ->get()
            ->groupBy('id_sales')
            ->map(function ($grup) {
                return $grup->pluck('total_jumlah', 'kuartal')->toArray();
            })->toArray();

        $pengguna = User::select('id_sales', 'username')->get()->keyBy('id_sales')->toArray();

        return view('crm.closedwin.index', compact('dataRingkasan', 'pengguna', 'tahunDipilih'));
    }

    public function detailRingkasan($id)
    {
        $data = Peluang::where('id_sales', $id)
            ->where('tahap', 'merah')
            ->with('aktivitas')
            ->get();
        return view('crm.closedwin.detail', compact('data'));
    }
}
