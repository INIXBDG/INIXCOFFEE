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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PeluangController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $allowedJabatan = ['Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting', 'GM', 'Direktur Utama', 'Direktur'];
        $materi = Materi::where('status', '!=', 'Nonaktif')->get();
        $aktivitas = Aktivitas::where('id_sales', $user->id_sales)->whereNull('id_peluang')->get();

        if ($user->jabatan === 'Sales') {
            $idSales = $user->id_sales;
            $data = Peluang::where('id_sales', $idSales)->get();
            $Perusahaan = Perusahaan::where('sales_key', $idSales)->get();
        } elseif (in_array($user->jabatan, $allowedJabatan)) {
            $data = Peluang::all();
            $Perusahaan = Perusahaan::all();
        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return view('crm.peluang.index', compact('data', 'Perusahaan', 'materi', 'aktivitas'));
    }

    public function indexJson()
    {
        try {
            $user = Auth::user();
            $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM', 'SPV Sales'];

            if ($user->jabatan === 'Sales') {
                $idSales = $user->id_sales;
                $data = Peluang::where('id_sales', $idSales)
                    ->with('materiRelation', 'rkm')
                    ->select('id', 'materi', 'harga', 'netsales', 'pax', 'periode_mulai', 'periode_selesai', 'tahap', 'created_at', 'id_rkm', 'id_sales')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($item) {
                        $item->periode = $item->periode_mulai . ' s/d ' . $item->periode_selesai;
                        $rkm = RKM::with('perusahaan')->where('id', $item->id_rkm)->first();
                        $item->rkm_data = $rkm ? $rkm : null;
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
                $data = Peluang::select('id', 'materi', 'harga', 'netsales', 'pax', 'periode_mulai', 'periode_selesai', 'tahap', 'created_at', 'id_rkm', 'id_sales')
                    ->with('materiRelation', 'rkm')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($item) {
                        $item->periode = $item->periode_mulai . ' s/d ' . $item->periode_selesai;
                        $rkm = RKM::with('perusahaan')->where('id', $item->id_rkm)->first();
                        $item->rkm_data = $rkm ? $rkm : null;
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
        // Ambil peluang dan relasi terkait
        $peluang = Peluang::with(['materiRelation', 'rkm', 'aktivitas'])
            ->where('id', $id)
            ->firstOrFail();

        // Normalisasi data RKM
        if ($peluang->rkm) {
            $peluang->rkm->tanggal_awal_day = $peluang->rkm->tanggal_awal ? date('d', strtotime($peluang->rkm->tanggal_awal)) : null;
            $peluang->rkm->tanggal_awal_month = $peluang->rkm->tanggal_awal ? date('n', strtotime($peluang->rkm->tanggal_awal)) : null;
            $peluang->rkm->tanggal_awal_year = $peluang->rkm->tanggal_awal ? date('Y', strtotime($peluang->rkm->tanggal_awal)) : null;
        }

        $materi = Materi::where('status', '!=', 'Nonaktif')->get();

        $netsales = perhitunganNetSales::with('trackingNetSales', 'approvedNetSales', 'peserta')
            ->where('id_rkm', $peluang->id_rkm)
            ->first();

        $regis = Regisform::where('id_peluang', $id)->first();

        // 🔹 Ambil semua aktivitas seperti $aktivitass
        $perusahaan = $peluang->perusahaan;

        $aktivitass = Aktivitas::with(['contact', 'peserta'])
            ->where('id_peluang', $id)
            ->where(function ($query) use ($perusahaan) {
                $query->whereIn('id_contact', $perusahaan->contacts->pluck('id'))
                    ->orWhereIn('id_peserta', $perusahaan->peserta->pluck('id'));
            })
            ->orderByDesc('created_at')
            ->get();

        $user = Auth::user();
        $aktivitasTambahan = Aktivitas::where('id_sales', $user->id_sales)->whereNull('id_peluang')->get();

        $data = Perusahaan::with(['contacts', 'peserta'])->where('id', $perusahaan->id)->firstOrFail();
        $items = [];
        foreach ($data->contacts as $contact) {
            $items[] = [
                'id' => $contact->id,
                'nama' => $contact->nama,
                'type' => 'contact',
                'label' => "[Contact] " . $contact->nama . " (" . ($contact->email ?? 'Tidak ada email') . ")"
            ];
        }
        foreach ($data->peserta as $peserta) {
            $items[] = [
                'id' => $peserta->id,
                'nama' => $peserta->nama,
                'type' => 'peserta',
                'label' => "[Peserta] " . $peserta->nama . " (" . ($peserta->email ?? 'Tidak ada email') . ")"
            ];
        }
        usort($items, function ($a, $b) {
            return strcasecmp($a['label'], $b['label']);
        });

        // dd($peluang);
        return view('crm.peluang.detail', compact(
            'peluang',
            'aktivitass',
            'materi',
            'netsales',
            'regis',
            'items',
            'aktivitasTambahan'
        ));
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

        $contactIds = $contacts->pluck('id')->toArray();
        $pesertaIds = $peserta->pluck('id')->toArray();

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
            $peluang = Peluang::with(
                'rkm',
                'rkm.perhitunganNetSales',
                'rkm.exam',
                'rkm.outstanding',
                'rkm.registrasi',
                'rkm.analisisrkm'
            )->findOrFail($id);

            $deletedBy = Auth::user()->karyawan->kode_karyawan ?? null;
            $now = Carbon::now();

            if ($peluang->rkm) {
                $rkm = $peluang->rkm;

                if ($rkm->perhitunganNetSales && $rkm->perhitunganNetSales->isNotEmpty()) {
                    foreach ($rkm->perhitunganNetSales as $item) {
                        $item->update([
                            'deleted_at' => $now,
                            'deleted_by' => $deletedBy,
                        ]);
                    }
                }

                if ($rkm->registrasi && $rkm->registrasi->isNotEmpty()) {
                    foreach ($rkm->registrasi as $item) {
                        $item->update([
                            'deleted_at' => $now,
                            'deleted_by' => $deletedBy,
                        ]);
                    }
                }

                if (!empty($rkm->exam)) {
                    $rkm->exam->update([
                        'deleted_at' => $now,
                        'deleted_by' => $deletedBy,
                    ]);
                }

                if (!empty($rkm->outstanding)) {
                    $rkm->outstanding->update([
                        'deleted_at' => $now,
                        'deleted_by' => $deletedBy,
                    ]);
                }

                if (!empty($rkm->analisisrkm)) {
                    $rkm->analisisrkm->update([
                        'deleted_at' => $now,
                        'deleted_by' => $deletedBy,
                    ]);
                }

                $rkm->update([
                    'deleted_at' => $now,
                    'deleted_by' => $deletedBy,
                ]);
            }

            $peluang->update([
                'lost' => $now,
                'tahap' => 'lost',
                'deleted_at' => $now,
                'deleted_by' => $deletedBy,
            ]);

            Aktivitas::where('id_peluang', $id)
                ->update([
                    'deleted_at' => $now,
                    'deleted_by' => $deletedBy,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Peluang dan semua relasi berhasil di-soft delete.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus peluang atau relasi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'materi' => 'required|string|max:255',
                'catatan' => 'nullable|string|max:255',
                'harga' => 'required|numeric|min:0',
                'final' => 'required|numeric|min:0',
                'pax' => 'required|integer|min:1',
                'periode_mulai' => 'required|date',
                'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
                'tentatif' => 'nullable|boolean',
                'id_aktivitas' => 'nullable|array',
                'id_aktivitas.*' => 'integer|exists:aktivitas,id',
            ]);

            // Start a database transaction
            DB::beginTransaction();

            // Find the Peluang record
            $peluang = Peluang::findOrFail($id);

            // Find the related RKM record
            $rkm = RKM::where('id', $peluang->id_rkm)->first();
            if (!$rkm) {
                throw new \Exception('RKM record not found for this Peluang.');
            }

            // Update RKM
            $rkm->materi_key = $request->materi;
            $rkm->harga_jual = $request->harga;
            $rkm->tanggal_awal = $request->periode_mulai;
            $rkm->tanggal_akhir = $request->periode_selesai;
            $rkm->pax = $request->pax;
            $rkm->isi_pax = $request->pax;
            $rkm->exam = $request->exam;
            $rkm->authorize = $request->authorize;
            $rkm->event = $request->event;
            $rkm->metode_kelas = $request->metode_kelas;
            $rkm->save();
            
            $final = $validated['final'] - ($validated['final'] * 11 / 100);

            // Update Peluang
            $peluang->update([
                'materi' => $validated['materi'],
                'catatan' => $validated['catatan'],
                'harga' => $validated['harga'],
                'final' => $final,
                'netsales' => $final,
                'pax' => $validated['pax'],
                'periode_mulai' => $validated['periode_mulai'],
                'periode_selesai' => $validated['periode_selesai'],
                'tentatif' => $validated['tentatif'] ?? false,
            ]);

            // Update Aktivitas: Set id_peluang only for newly selected activities
            $selectedAktivitasIds = $request->input('id_aktivitas', []);
            if (!empty($selectedAktivitasIds)) {
                foreach ($selectedAktivitasIds as $aktivitasId) {
                    $aktivitas = Aktivitas::find($aktivitasId);
                    if ($aktivitas) {
                        $aktivitas->id_peluang = $id;
                        $aktivitas->save();
                    } else {
                        Log::warning("Aktivitas with ID {$aktivitasId} not found.");
                    }
                }
            }

            // Commit the transaction
            DB::commit();

            return back()->with([
                'message' => 'Lead berhasil diperbarui.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in Peluang update: ', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Peluang: ' . $e->getMessage());
            return back()->with([
                'error' => 'Gagal memperbarui lead: ' . $e->getMessage(),
            ])->withInput();
        }
    }
    
    public function updateTahap($id, Request $request)
    {
        $peluang = Peluang::with(
            'rkm',
            'rkm.perhitunganNetSales',
            'rkm.exam',
            'rkm.outstanding',
            'rkm.registrasi',
            'rkm.analisisrkm'
        )->where('id', $id)->firstOrFail();

        DB::transaction(function () use ($peluang, $request) {
            $now = Carbon::now();
            $deletedBy = Auth::user()->karyawan->kode_karyawan ?? null;

            if ($request->tahap === 'biru') {
                $peluang->tahap = 'biru';
                $peluang->biru = $now;
                $peluang->desc_lost = null;

                if ($peluang->rkm) {
                    $peluang->rkm->status = '1';
                    $peluang->rkm->save();
                }
            }

            if ($request->tahap === 'lost') {
                $peluang->tahap = 'lost';
                $peluang->lost = $now;
                $peluang->desc_lost = $request->input('desc_lost');

                if ($peluang->rkm) {
                    $rkm = $peluang->rkm;

                    if ($rkm->perhitunganNetSales && $rkm->perhitunganNetSales->isNotEmpty()) {
                        foreach ($rkm->perhitunganNetSales as $item) {
                            $item->update([
                                'deleted_at' => $now,
                                'deleted_by' => $deletedBy,
                            ]);
                        }
                    }

                    if ($rkm->registrasi && $rkm->registrasi->isNotEmpty()) {
                        foreach ($rkm->registrasi as $item) {
                            $item->update([
                                'deleted_at' => $now,
                                'deleted_by' => $deletedBy,
                            ]);
                        }
                    }

                    if (!empty($rkm->exam)) {
                        $rkm->exam->update([
                            'deleted_at' => $now,
                            'deleted_by' => $deletedBy,
                        ]);
                    }

                    if (!empty($rkm->outstanding)) {
                        $rkm->outstanding->update([
                            'deleted_at' => $now,
                            'deleted_by' => $deletedBy,
                        ]);
                    }

                    if (!empty($rkm->analisisrkm)) {
                        $rkm->analisisrkm->update([
                            'deleted_at' => $now,
                            'deleted_by' => $deletedBy,
                        ]);
                    }

                    $rkm->update([
                        'deleted_at' => $now,
                        'deleted_by' => $deletedBy,
                    ]);
                }
            }

            if ($request->tahap === 'merah') {
                $peluang->tahap = 'merah';

                $inputFinal = $request->input('final');
                $final = $inputFinal - ($inputFinal * 11 / 100);

                $peluang->final = $final;
                $peluang->netsales = $final;

                $peluang->merah = $now;
                $peluang->desc_lost = null;

                if ($peluang->rkm) {
                    $peluang->rkm->status = '0';
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
                DB::raw('SUM(netsales * pax) as total_jumlah'),
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

    public function detailRingkasan(Request $request, $id)
    {
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        $query = Peluang::where('id_sales', $id)
            ->where('tahap', 'merah');

        if ($tahun) {
            $query->whereYear('periode_mulai', $tahun);
        }

        if ($bulan) {
            $query->whereMonth('periode_mulai', $bulan);
        }

        $data = $query->orderBy('periode_mulai', 'asc')->get();

        return view('crm.closedwin.detail', compact('data', 'tahun', 'bulan', 'id'));
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
        Log::info("[PA] Start storePaymentAdvance", ['request' => $request->all()]);

        $request->validate([
            'id_rkm' => 'required|numeric',
            'id_peluang' => 'required|numeric',

            'transportasi' => 'nullable|numeric',
            'jenis_transportasi' => 'nullable|string',

            'akomodasi_peserta' => 'nullable|numeric',
            'akomodasi_tim' => 'nullable|numeric',
            'keterangan_akomodasi_tim' => 'nullable|string',
            
            'fresh_money' => 'nullable|numeric',
            'entertaint' => 'nullable|numeric',
            'keterangan_entertaint' => 'nullable|string',
            'souvenir' => 'nullable|numeric',
            'cashback' => 'nullable|numeric',
            
            'sewa_laptop' => 'nullable|numeric',
            'tgl_pa' => 'required|date',
            'tipe_pembayaran' => 'required|string',
            'deskripsi_tambahan' => 'nullable|string',
        ]);

        Log::info("[PA] Validation passed");

        // Check existing netsales
        $existingNetSales = perhitunganNetSales::where('id_rkm', $request->id_rkm)->first();
        Log::info("[PA] Existing Net Sales", ['exists' => $existingNetSales ? true : false]);

        $idTracking = null;

        if (!$existingNetSales) {
            Log::info("[PA] No existing netsales, creating new tracking");

            $tracking = new trackingNetSales();
            $tracking->id_rkm = $request->id_rkm;
            $tracking->save();

            $idTracking = $tracking->id;

            Log::info("[PA] Tracking created", ['tracking_id' => $idTracking]);

        } else {
            Log::info("[PA] Netsales exists, fetching existing tracking");

            $tracking = trackingNetSales::where('id_rkm', $existingNetSales->id_rkm)->first();
            $idTracking = $tracking->id;

            Log::info("[PA] Using existing tracking", ['tracking_id' => $idTracking]);
        }

        // Save payment advance
        Log::info("[PA] Saving new Net Sales record");

        $netSales = new perhitunganNetSales();
        $netSales->id_rkm = $request->id_rkm;
        
        $netSales->transportasi = $request->transportasi;
        $netSales->jenis_transportasi = $request->jenis_transportasi;

        $netSales->akomodasi_peserta = $request->akomodasi_peserta;
        $netSales->akomodasi_tim = $request->akomodasi_tim;
        $netSales->keterangan_akomodasi_tim = $request->keterangan_akomodasi_tim;
        
        $netSales->fresh_money = $request->fresh_money;
        $netSales->entertaint = $request->entertaint;
        $netSales->keterangan_entertaint = $request->keterangan_entertaint;
        $netSales->souvenir = $request->souvenir;
        $netSales->cashback = $request->cashback;
        
        $netSales->sewa_laptop = $request->sewa_laptop;
        $netSales->tipe_pembayaran = $request->tipe_pembayaran;
        $netSales->tgl_pa = $request->tgl_pa;
        $netSales->deskripsi_tambahan = $request->deskripsi_tambahan;

        $netSales->id_tracking = $idTracking;
        $netSales->save();

        Log::info("[PA] Net Sales saved", ['net_sales_id' => $netSales->id]);

        // Notify SPV
        Log::info("[PA] Looking for SPV Sales");

        $spv = karyawan::where('jabatan', 'SPV Sales')->first();

        if ($spv) {
            Log::info("[PA] SPV found", ['spv' => $spv->kode_karyawan]);

            $user = User::whereHas('karyawan', function ($q) use ($spv) {
                $q->where('kode_karyawan', $spv->kode_karyawan);
            })->first();

            if ($user) {
                Log::info("[PA] User found for SPV", ['user_id' => $user->id]);

                $dummyComment = (object)[
                    'karyawan_key' => auth()->user()->karyawan->id ?? null,
                    'content' => 'Pengajuan Payment Advance baru oleh Sales, anda dimohon untuk melakukan persetujuan.',
                    'materi_key' => null,
                    'rkm_key' => $request->id_rkm,
                ];

                Log::info("[PA] Sending notification");

                $url = url('paymentAdvance.index');
                $path = "/crm/peluang/detail/" . $request->id_peluang;
                $receiverUsers = $user->id;
                Notification::send($user, new CommentNotification($dummyComment, $url, $path, $receiverUsers));

                Log::info("[PA] Path ($path) and URL ($url) included in notification");
            }
        } else {
            Log::warning("[PA] No SPV Sales found");
        }

        Log::info("[PA] Completed successfully");

        return redirect()->back()->with('success', 'Data payment advance berhasil disimpan.');
    }

}
