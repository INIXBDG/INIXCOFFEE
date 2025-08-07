<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;

use App\Models\Aktivitas;
use App\Models\Contact;
use App\Models\Materi;
use App\Models\Peluang;
use App\Models\Perusahaan;
use App\Models\User;
use Carbon\Carbon;
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
        $aktivitas = Aktivitas::where('id_sales', $user->id_sales)
            ->whereNull('id_peluang')
            ->get();

        if ($user->jabatan === 'Sales') {
            $idSales = $user->id_sales;
            $data = Peluang::where('id_sales', $idSales)->get();
            $contact = Perusahaan::where('sales_key', $idSales)->get();
        } elseif (in_array($user->jabatan, $allowedJabatan)) {
            $data = Peluang::all();
            $contact = Perusahaan::all();
        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return view('crm.peluang.index', compact('data', 'contact', 'materi', 'aktivitas'));
    }

    public function indexJson()
    {
        try {
            $user = Auth::user();
            $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM'];

            if ($user->jabatan === 'Sales') {
                $idSales = $user->id_sales;
                $data = Peluang::where('id_sales', $idSales)
                    ->select('id', 'materi', 'harga', 'netsales', 'pax', 'periode_mulai', 'periode_selesai', 'tahap')
                    ->get()
                    ->map(function ($item) {
                        $item->periode = $item->periode_mulai . ' s/d ' . $item->periode_selesai; // Fixed concatenation
                        return $item;
                    });
            } elseif (in_array($user->jabatan, $allowedJabatan)) {
                $data = Peluang::select('id', 'materi', 'harga', 'netsales', 'pax', 'periode_mulai', 'periode_selesai', 'tahap')
                    ->get()
                    ->map(function ($item) {
                        $item->periode = $item->periode_mulai . ' s/d ' . $item->periode_selesai; // Fixed concatenation
                        return $item;
                    });
            } else {
                return response()->json([
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], 500);
        }
    }

    public function detail($id)
    {
        $aktivitas = Aktivitas::where('id_peluang', $id)->get();
        $peluang = Peluang::where('id', $id)->first();
        $materi = Materi::all();
        return view('crm.peluang.detail', compact('peluang', 'aktivitas', 'materi'));
    }

    public function AmbilAktivitas($id)
    {
        $aktivitas = Aktivitas::where('id_contact', $id)
            ->whereNull('id_peluang')
            ->with('perusahaan')
            ->get();

        $result = $aktivitas->map(function ($a) {
            return [
                'id' => $a->id,
                'kontak' => $a->perusahaan->nama_perusahaan ?? '-',
                'aktivitas' => ucfirst($a->aktivitas),
                'subject' => $a->subject,
                'deskripsi' => $a->deskripsi,
                'waktu' => \Carbon\Carbon::parse($a->waktu_aktivitas)->format('d/m/Y'),
            ];
        });

        return response()->json($result);
    }


    public function store(Request $request)
    {
        $request->merge([
            'harga' => preg_replace('/[^0-9]/', '', $request->harga),
            'netsales' => preg_replace('/[^0-9]/', '', $request->netsales),
        ]);

        $validated = $request->validate([
            'id_contact' => 'required|integer|exists:perusahaans,id',
            'materi' => 'required|string|max:255',
            'catatan' => 'nullable|string|max:255',
            'harga' => 'required|numeric',
            'netsales' => 'required|numeric',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
            'pax' => 'required|numeric|min:1',
            'id_aktivitas' => 'nullable|array',
            'id_aktivitas.*' => 'integer|exists:aktivitas,id',
        ]);

        $validated['id_sales'] = $request->input('id_sales', auth()->user()->id_sales ?? null);

        $peluang = Peluang::create($validated);

        if ($request->filled('id_aktivitas')) {
            Aktivitas::whereIn('id', $request->id_aktivitas)
                ->update(['id_peluang' => $peluang->id]);
        }

        return back()->with([
            'message' => 'Peluang berhasil dibuat dan aktivitas berhasil dikaitkan.',
            'data' => $peluang,
        ]);
    }


    public function delete($id)
    {
        try {
            $peluang = Peluang::findOrFail($id);
            $peluang->delete();

            $aktivitas = Aktivitas::where('id_peluang', $id)->get();
            foreach ($aktivitas as $item) {
                $item->delete();
            }

            return response()->json(['message' => 'Peluang dan aktivitas terkait berhasil dihapus.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghapus peluang atau aktivitas terkait.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'materi' => 'required|string|max:255',
            'catatan' => 'nullable|string|max:255',
            'harga' => 'required|numeric|min:0',
            'netsales' => 'required|numeric|min:0',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
            'pax' => 'required|numeric|min:1',
        ]);

        $peluang = Peluang::findOrFail($id);
        $peluang->update($validated);

        return back()->with([
            'message' => 'Lead berhasil diperbarui.',
        ]);
    }

    public function updateTahap($id, Request $request)
    {
        $peluang = Peluang::where('id', $id)->first();

        if ($request->tahap === 'biru') {
            $peluang->tahap = $request->tahap;
            $time = Carbon::now();
            $peluang->biru = $time;

            // Kosongkan kolom lost yang sebelumnya mungkin ada
            $peluang->desc_lost = null;
        }

        if ($request->tahap === 'lost') {
            $peluang->tahap = $request->tahap;
            $time = Carbon::now();
            $peluang->lost = $time;
            $peluang->desc_lost = $request->desc_lost; // Simpan deskripsi lost
        }

        if ($request->tahap === 'merah') {
            $peluang->tahap = $request->tahap;
            $peluang->final = $request->final;
            $time = Carbon::now();
            $peluang->merah = $time;

            // Kosongkan kolom lost yang sebelumnya mungkin ada
            $peluang->desc_lost = null;
        }

        $peluang->update();

        return back()->with([
            'message' => 'Tahap berhasil di perbarui.',
        ]);
    }

    public function ringkasanPeluang(Request $request)
    {
        $tahunDipilih = $request->query('tahun', now()->year);

        $dataRingkasan = Peluang::whereNotNull('merah')
            ->whereYear('merah', $tahunDipilih)
            ->select(
                'id_sales',
                DB::raw('CASE
                WHEN MONTH(merah) BETWEEN 1 AND 3 THEN "TR1"
                WHEN MONTH(merah) BETWEEN 4 AND 6 THEN "TR2"
                WHEN MONTH(merah) BETWEEN 7 AND 9 THEN "TR3"
                WHEN MONTH(merah) BETWEEN 10 AND 12 THEN "TR4"
            END as triwulan'),
                DB::raw('SUM(final) as total_jumlah')
            )
            ->groupBy('id_sales', 'triwulan')
            ->get()
            ->groupBy('id_sales')
            ->map(function ($grup) {
                return $grup->pluck('total_jumlah', 'triwulan')->toArray();
            })->toArray();

        $pengguna = User::select('id_sales', 'username')->get()->keyBy('id_sales')->toArray();

        return view('crm.closedwin.index', compact('dataRingkasan', 'pengguna', 'tahunDipilih'));
    }


    public function detailRingkasan($id)
    {
        $data = Peluang::where('id_sales', $id)
            ->where('tahap', 'merah')
            ->with('aktivitas')
            ->with('perusahaan')
            ->get();
        return view('crm.closedwin.detail', compact('data'));
    }

    public function ringkasanPeluanglost(Request $request)
    {
        $tahunDipilih = $request->query('tahun', now()->year);

        $dataRingkasan = Peluang::whereNotNull('lost')
            ->whereYear('lost', $tahunDipilih)
            ->select(
                'id_sales',
                DB::raw('CASE
                    WHEN MONTH(lost) BETWEEN 1 AND 3 THEN "TR1"
                    WHEN MONTH(lost) BETWEEN 4 AND 6 THEN "TR2"
                    WHEN MONTH(lost) BETWEEN 7 AND 9 THEN "TR3"
                    WHEN MONTH(lost) BETWEEN 10 AND 12 THEN "TR4"
                END as triwulan'),
                DB::raw('SUM(harga * pax) as total_jumlah')
            )
            ->groupBy('id_sales', 'triwulan')
            ->get()
            ->groupBy('id_sales')
            ->map(function ($grup) {
                return $grup->pluck('total_jumlah', 'triwulan')->toArray();
            })
            ->toArray();

        $pengguna = User::select('id_sales', 'username')->get()->keyBy('id_sales')->toArray();

        return view('crm.closedlost.index', compact('dataRingkasan', 'pengguna', 'tahunDipilih'));
    }

    public function detailRingkasanlost($id)
    {
        $data = Peluang::where('id_sales', $id)
            ->where('tahap', 'lost')
            ->with('aktivitas')
            ->with('perusahaan')
            ->get();
        return view('crm.closedlost.detail', compact('data'));
    }
}
