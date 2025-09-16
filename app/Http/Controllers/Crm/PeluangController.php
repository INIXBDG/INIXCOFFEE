<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;

use App\Models\Aktivitas;
use App\Models\Contact;
use App\Models\Materi;
use App\Models\Peluang;
use App\Models\Peserta;
use App\Models\Perusahaan;
use App\Models\RKM;
use App\Models\User;
use App\Models\perhitunganNetSales;
use App\Models\karyawan;
use App\Models\RegisForm;
use App\Models\Registrasi;
use App\Models\trackingNetSales;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Notifications\CommentNotification;
use Illuminate\Support\Facades\Notification;

class PeluangController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM', 'Sales', 'Direktur Utama', 'Direktur'];
        $materi = Materi::all();
        $aktivitas = Aktivitas::where('id_sales', $user->id_sales)->whereNull('id_peluang')->get();

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
                    ->with('materiRelation')
                    ->select('id', 'materi', 'harga', 'netsales', 'pax', 'periode_mulai', 'periode_selesai', 'tahap', 'created_at', 'id_rkm')
                    ->orderBy('created_at','desc')
                    ->get()
                    ->map(function ($item) {
                        $item->periode = $item->periode_mulai . ' s/d ' . $item->periode_selesai;
                        // Fetch RKM data based on id_rkm
                        $rkm = RKM::with('perusahaan')->where('id', $item->id_rkm)->first();
                        // Append RKM data or null if not found
                        $item->rkm_data = $rkm ? $rkm : null;
                        // Create new rkm_formatted data if rkm exists
                        $item->rkm_formatted = $rkm
                            ? [
                                'materi_key' => $rkm->materi_key,
                                'metode_kelas' => $rkm->metode_kelas === 'Offline' ? 'off' : ($rkm->metode_kelas === 'Inhouse Bandung' ? 'inhb' : ($rkm->metode_kelas === 'Inhouse Luar Bandung' ? 'inhlb' : 'vir')),
                                'tanggal_awal_day' => $rkm->tanggal_awal ? date('d', strtotime($rkm->tanggal_awal)) : null,
                                'tanggal_awal_month' => $rkm->tanggal_awal ? date('n', strtotime($rkm->tanggal_awal)) : null,
                                'tanggal_awal_year' => $rkm->tanggal_awal ? date('Y', strtotime($rkm->tanggal_awal)) : null,
                            ]
                            : null;
                        return $item;
                    });
            } elseif (in_array($user->jabatan, $allowedJabatan)) {
                $data = Peluang::select('id', 'materi', 'harga', 'netsales', 'pax', 'periode_mulai', 'periode_selesai', 'tahap', 'created_at', 'id_rkm')
                    ->with('materiRelation')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($item) {
                        $item->periode = $item->periode_mulai . ' s/d ' . $item->periode_selesai;
                        // Fetch RKM data based on id_rkm
                        $rkm = RKM::with('perusahaan')->where('id', $item->id_rkm)->first();
                        // Append RKM data or null if not found
                        $item->rkm_data = $rkm ? $rkm : null;
                        // Create new rkm_formatted data if rkm exists
                        $item->rkm_formatted = $rkm
                            ? [
                                'materi_key' => $rkm->materi_key,
                                'metode_kelas' => $rkm->metode_kelas === 'Offline' ? 'off' : ($rkm->metode_kelas === 'Inhouse Bandung' ? 'inhb' : ($rkm->metode_kelas === 'Inhouse Luar Bandung' ? 'inhlb' : 'vir')),
                                'tanggal_awal_day' => $rkm->tanggal_awal ? date('d', strtotime($rkm->tanggal_awal)) : null,
                                'tanggal_awal_month' => $rkm->tanggal_awal ? date('n', strtotime($rkm->tanggal_awal)) : null,
                                'tanggal_awal_year' => $rkm->tanggal_awal ? date('Y', strtotime($rkm->tanggal_awal)) : null,
                            ]
                            : null;
                        return $item;
                    });
            } else {
                return response()->json(
                    [
                        'error' => 'Unauthorized access.',
                    ],
                    403,
                );
            }

            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
                ],
                500,
            );
        }
    }

    public function detail($id)
    {
        $aktivitas = Aktivitas::where('id_peluang', $id)->get();
        $peluang = Peluang::with(['materiRelation', 'rkm'])
            ->where('id', $id)
            ->first();
        if ($peluang && $peluang->rkm) {
            $peluang->rkm->metode_kelas = $peluang->rkm->metode_kelas === 'Offline' ? 'off' : ($peluang->rkm->metode_kelas === 'Inhouse Bandung' ? 'inhb' : ($peluang->rkm->metode_kelas === 'Inhouse Luar Bandung' ? 'inhlb' : 'vir'));
            $peluang->rkm->tanggal_awal_day = $peluang->rkm->tanggal_awal ? date('d', strtotime($peluang->rkm->tanggal_awal)) : null;
            $peluang->rkm->tanggal_awal_month = $peluang->rkm->tanggal_awal ? date('n', strtotime($peluang->rkm->tanggal_awal)) : null;
            $peluang->rkm->tanggal_awal_year = $peluang->rkm->tanggal_awal ? date('Y', strtotime($peluang->rkm->tanggal_awal)) : null;
        }
        $materi = Materi::all();
        $netsales = perhitunganNetSales::with('trackingNetSales', 'approvedNetSales', 'peserta')->where('id_rkm', $peluang->id_rkm)->get();
        $regis = Regisform::where('id_peluang', $id)->first();

        $ids_peserta_yang_sudah_ada = perhitunganNetSales::where('id_rkm', $peluang->rkm->id)->pluck('id_peserta');
        $regisuser = Registrasi::with('peserta')->where('id_rkm', $peluang->rkm->id)->whereNotIn('id_peserta', $ids_peserta_yang_sudah_ada)->get();

        // dd($netsales);
        return view('crm.peluang.detail', compact('peluang', 'aktivitas', 'materi', 'netsales', 'regis', 'regisuser'));
    }

    public function AmbilAktivitas($id)
    {
        $contacts = Contact::where('id_perusahaan', $id)
            ->select('id', 'nama', 'email', 'divisi')
            ->get()
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'nama' => $contact->nama,
                    'email' => $contact->email,
                    'divisi' => $contact->divisi,
                    'type' => 'contact',
                ];
            });

        // Ambil semua peserta berdasarkan perusahaan
        $peserta = Peserta::where('perusahaan_key', $id)
            ->select('id', 'nama', 'email')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nama' => $p->nama,
                    'email' => $p->email,
                    'divisi' => 'C-Peserta',
                    'type' => 'peserta',
                ];
            });

        // Ambil semua ID contact dan peserta
        $contactIds = $contacts->pluck('id')->toArray();
        $pesertaIds = $peserta->pluck('id')->toArray();

        // Ambil semua aktivitas berdasarkan id_contact atau id_peserta
        $aktivitas = Aktivitas::with(['contact', 'peserta'])
            ->where(function ($query) use ($contactIds, $pesertaIds) {
                if (!empty($contactIds)) {
                    $query->whereIn('id_contact', $contactIds);
                }
                if (!empty($pesertaIds)) {
                    $query->orWhereIn('id_peserta', $pesertaIds);
                }
            })
            ->whereNull('id_peluang')
            ->orderByDesc('created_at')
            ->get();

        // Format data untuk response JSON
        $result = $aktivitas->map(function ($a) {
            return [
                'id' => $a->id,
                'kontak' => $a->contact->nama ?? ($a->peserta->nama ?? '-'),
                'aktivitas' => ucfirst($a->aktivitas),
                'subject' => $a->subject,
                'deskripsi' => $a->deskripsi ?? '-',
                'waktu' => \Carbon\Carbon::parse($a->waktu_aktivitas)->format('Y-m-d'),
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

        // Validasi data untuk tabel Peluang
        $validated = $request->validate([
            'id_contact' => 'required|integer|exists:perusahaans,id',
            'materi' => 'required|string|max:255',
            'catatan' => 'nullable|string|max:255',
            'harga' => 'required|numeric',
            'netsales' => 'nullable',
            'periode_mulai' => 'nullable|date',
            'periode_selesai' => 'nullable|date|after_or_equal:periode_mulai',
            'pax' => 'required|numeric|min:1',
            'id_aktivitas' => 'nullable|array',
            'id_aktivitas.*' => 'integer|exists:aktivitas,id',
            'tentatif' => 'nullable|boolean',
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
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $bulanInt = (int) $start->format('n');

        try {
            Carbon::setLocale('id');
            $bulanNama = $start->translatedFormat('F');
            if (in_array(strtolower($bulanNama), ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'])) {
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
            'tanggal_awal' => $request->filled('periode_mulai') ? $request->periode_mulai : now()->toDateString(),
            'tanggal_akhir' => $request->filled('periode_selesai') ? $request->periode_selesai : now()->toDateString(),
            'bulan' => $bulanNama,
            'quartal' => $kuartal,
            'tahun' => $tahun,
            'status' => '2',
        ]);

        $rkm = RKM::create($rkmData);

        $validated['id_rkm'] = $rkm ? $rkm->id : null;

        $validated['id_sales'] = $request->input('id_sales', auth()->user()->id_sales ?? null);

        foreach (['periode_mulai', 'periode_selesai', 'netsales'] as $field) {
            if (empty($validated[$field])) {
                $validated[$field] = null;
            }
        }

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
            $rkm = RKM::where('id', $peluang->id_rkm)->first();
            $rkm->delete();
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
            'tentatif' => 'nullable|boolean',
        ]);

        $peluang = Peluang::findOrFail($id);

        $rkm = RKM::where('id', $peluang->id_rkm)->first();
        $rkm->materi_key = $request->materi;
        $rkm->harga_jual = $request->harga;
        $rkm->tanggal_awal = $request->periode_mulai;
        $rkm->tanggal_akhir = $request->periode_selesai;
        $rkm->pax = $request->pax;
        $rkm->isi_pax = $request->pax;
        $rkm->update();

        $peluang->update($validated);

        return back()->with([
            'message' => 'Lead berhasil diperbarui.',
        ]);
    }

    public function updateTahap($id, Request $request)
    {
        // Ambil peluang beserta relasi RKM jika ada (pastikan relation 'rkm' didefinisikan di model Peluang)
        $peluang = Peluang::with('rkm')->where('id', $id)->firstOrFail();

        DB::transaction(function () use ($peluang, $request) {
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
                DB::raw('SUM(final) as total_jumlah'),
            )
            ->groupBy('id_sales', 'triwulan')
            ->get()
            ->groupBy('id_sales')
            ->map(function ($grup) {
                return $grup->pluck('total_jumlah', 'triwulan')->toArray();
            })
            ->toArray();

        $pengguna = User::select('id_sales', 'username')->get()->keyBy('id_sales')->toArray();

        return view('crm.closedwin.index', compact('dataRingkasan', 'pengguna', 'tahunDipilih'));
    }

    public function detailRingkasan($id)
    {
        $data = Peluang::where('id_sales', $id)
            ->where('tahap', 'merah')
            ->with([
                'aktivitas',
                'materiRelation',
                'perusahaan',
                'rkm'
            ])
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
                DB::raw('SUM(harga * pax) as total_jumlah'),
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
        $data = Peluang::where('id_sales', $id)->where('tahap', 'lost')->with('aktivitas', 'materiRelation')->with('perusahaan')->get();
        return view('crm.closedlost.detail', compact('data'));
    }

    public function storePaymentAdvance(Request $request)
    {
        $request->validate([
            'id_rkm' => 'required|numeric',
            'id_peserta' => 'required|numeric',
            'hargaPenawaran' => 'required|numeric',
            'transportasi' => 'nullable|numeric',
            'penginapan' => 'nullable|numeric',
            'freshMoney' => 'nullable|numeric',
            'cashback' => 'nullable|numeric',
            'diskon' => 'nullable|numeric',
            'entertaint' => 'nullable|numeric',
            'souvenir' => 'nullable|numeric',
            'desc' => 'nullable',
            'tanggalPayment' => 'required|date',
            'tipePembayaran' => 'required|string',
        ]);

        $existingNetSales = perhitunganNetSales::where('id_rkm', $request->id_rkm)->first();

        $idTracking = null;

        if (!$existingNetSales) {
            // Jika BELUM ada, buat tracking baru
            $tracking = new trackingNetSales();
            $tracking->id_rkm = $request->id_rkm;
            $tracking->save();

            $idTracking = $tracking->id;
        } else {
            $tracking = trackingNetSales::where('id_rkm', $existingNetSales->id_rkm)->first();
            $idTracking = $tracking->id;
        }

        // Simpan data payment advance baru
        $netSales = new perhitunganNetSales();
        $netSales->id_rkm = $request->id_rkm;
        $netSales->id_peserta = $request->id_peserta;
        $netSales->harga_penawaran = $request->hargaPenawaran;
        $netSales->transportasi = $request->transportasi;
        $netSales->penginapan = $request->penginapan;
        $netSales->fresh_money = $request->freshMoney;
        $netSales->cashback = $request->cashback;
        $netSales->diskon = $request->diskon;
        $netSales->entertaint = $request->entertaint;
        $netSales->souvenir = $request->souvenir;
        $netSales->desc = $request->desc;
        $netSales->tgl_pa = $request->tanggalPayment;
        $netSales->tipe_pembayaran = $request->tipePembayaran;
        $netSales->id_tracking = $idTracking;
        $netSales->save();

        Peluang::updateNetSalesFromRkm($request->id_rkm);
        // Kirim notifikasi ke SPV Sales
        $spv = karyawan::where('jabatan', 'SPV Sales')->first();

        if ($spv) {
            $user = User::whereHas('karyawan', function ($q) use ($spv) {
                $q->where('kode_karyawan', $spv->kode_karyawan);
            })->first();

            if ($user) {
                $dummyComment = (object) [
                    'karyawan_key' => auth()->user()->karyawan->id ?? null,
                    'content' => 'Pengajuan Payment Advance baru oleh Sales, anda dimohon untuk melakukan persetujuan.',
                    'materi_key' => null,
                    'rkm_key' => $request->id_rkm,
                ];

                $url = url('paymentAdvance.index');
                $path = request()->path();

                Notification::send($user, new CommentNotification($dummyComment, $url, $path));
            }
        }

        return redirect()->back()->with('success', 'Data payment advance berhasil disimpan.');
    }
}
