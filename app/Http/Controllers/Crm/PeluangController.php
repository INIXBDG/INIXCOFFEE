<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;

use App\Models\Aktivitas;
use App\Models\Contact;
use App\Models\Materi;
use App\Models\Peluang;
use App\Models\Perusahaan;
use App\Models\RKM;
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
                    ->with('materi')
                    ->select('id', 'materi', 'harga', 'netsales', 'pax', 'periode_mulai', 'periode_selesai', 'tahap', 'created_at',)
                    ->get()
                    ->map(function ($item) {
                        $item->periode = $item->periode_mulai . ' s/d ' . $item->periode_selesai; // Fixed concatenation
                        return $item;
                    });
            } elseif (in_array($user->jabatan, $allowedJabatan)) {
                $data = Peluang::select('id', 'materi', 'harga', 'netsales', 'pax', 'periode_mulai', 'periode_selesai', 'tahap', 'created_at',)
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
        // Bersihkan input harga dan netsales agar hanya berisi angka
        $request->merge([
            'harga' => preg_replace('/[^0-9]/', '', $request->harga),
            'netsales' => preg_replace('/[^0-9]/', '', $request->netsales),
        ]);

        // Validasi data untuk tabel Peluang
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

        // Validasi data untuk tabel RKM
        $validatedRKM = $request->validate([
            'metode_kelas' => 'required|string|max:255',
            'event' => 'required|string|max:255',
            'exam' => 'required|in:0,1',
            'authorize' => 'required|in:0,1',
        ]);

        // Parse tanggal dengan Carbon
        try {
            $start = Carbon::parse($request->input('periode_mulai'));
        } catch (\Exception $e) {
            return back()->withErrors(['periode_mulai' => 'Format tanggal periode_mulai tidak valid.']);
        }

        $bulanNamaMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $bulanInt = (int) $start->format('n');

        try {
            Carbon::setLocale('id');
            $bulanNama = $start->translatedFormat('F');
            if (in_array(strtolower($bulanNama), [
                'january','february','march','april','may','june','july','august','september','october','november','december'
            ])) {
                $bulanNama = $bulanNamaMap[$bulanInt];
            }
        } catch (\Exception $e) {
            $bulanNama = $bulanNamaMap[$bulanInt];
        }

        // Hitung kuartal sebagai string "Q1".."Q4"
        if ($bulanInt >= 1 && $bulanInt <= 3) {
            $kuartal = 'Q1';
        } elseif ($bulanInt >= 4 && $bulanInt <= 6) {
            $kuartal = 'Q2';
        } elseif ($bulanInt >= 7 && $bulanInt <= 9) {
            $kuartal = 'Q3';
        } else {
            $kuartal = 'Q4';
        }

        $tahun = $start->format('Y');


        // Siapkan data RKM, termasuk yang diambil dari request dan user login, plus bulan, kuartal, tahun
        $rkmData = array_merge($validatedRKM, [
            'sales_key' => auth()->user()->id_sales ?? null,
            'materi_key' => $request->materi,
            'perusahaan_key' => $request->id_contact,
            'harga_jual' => $request->harga,
            'pax' => $request->pax,
            'isi_pax' => $request->pax,
            'tanggal_awal' => $request->periode_mulai,
            'tanggal_akhir' => $request->periode_selesai,
            'bulan' => $bulanNama,
            'quartal' => $kuartal,
            'tahun' => $tahun,
            'status' => '2',
        ]);

        // Buat record RKM lebih dulu supaya mendapatkan id
        $rkm = RKM::create($rkmData);

        // Masukkan id_rkm ke data untuk Peluang
        $validated['id_rkm'] = $rkm->id;

        // Isi id_sales jika tidak ada di input, ambil dari user yang login
        $validated['id_sales'] = $request->input('id_sales', auth()->user()->id_sales ?? null);

        // Buat record Peluang sekarang dengan id_rkm yang sudah ada
        $peluang = Peluang::create($validated);

        // Jika ada aktivitas yang ingin dikaitkan, update id_peluang pada aktivitas tersebut
        if ($request->filled('id_aktivitas')) {
            Aktivitas::whereIn('id', $request->id_aktivitas)->update(['id_peluang' => $peluang->id]);
        }

        // Redirect kembali dengan pesan sukses dan data peluang
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
        // Ambil peluang beserta relasi RKM jika ada (pastikan relation 'rkm' didefinisikan di model Peluang)
        $peluang = Peluang::with('rkm')->where('id', $id)->firstOrFail();

        DB::transaction(function() use ($peluang, $request) {
            $now = Carbon::now();

            // Reset kolom-kolom waktu yang relevan apabila perlu (optional)
            // $peluang->biru = null; $peluang->merah = null; $peluang->lost = null;

            if ($request->tahap === 'biru') {
                $peluang->tahap = 'biru';
                $peluang->biru = $now;
                $peluang->desc_lost = null;

                // Update status RKM jika ada relasi
                if ($peluang->rkm) {
                    $peluang->rkm->status = '1'; // biru -> status '1'
                    $peluang->rkm->save();
                }
            }

            if ($request->tahap === 'lost') {
                $peluang->tahap = 'lost';
                $peluang->lost = $now;
                $peluang->desc_lost = $request->input('desc_lost');

                if ($peluang->rkm) {
                    $peluang->rkm->status = '3'; // lost -> status '3'
                    $peluang->rkm->save();
                }
            }

            if ($request->tahap === 'merah') {
                $peluang->tahap = 'merah';
                $peluang->final = $request->input('final');
                $peluang->merah = $now;
                $peluang->desc_lost = null;

                if ($peluang->rkm) {
                    $peluang->rkm->status = '0'; // merah -> status '0'
                    $peluang->rkm->save();
                }
            }

            $peluang->save();
        });

        return back()->with([
            'message' => 'Tahap berhasil diperbarui dan status RKM telah di-sync.',
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
