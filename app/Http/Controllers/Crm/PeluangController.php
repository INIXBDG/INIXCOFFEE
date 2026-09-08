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
use App\Models\eksam;
use App\Models\outstanding;
use App\Models\kelasanalisis;
use App\Models\trackingNetSales;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Notifications\CommentNotification;
use App\Notifications\PoReminder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Gate;

class PeluangController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View Peluang', ['only' => ['index', 'indexJson']]);
        $this->middleware('permission:Store Peluang', ['only' => ['store']]);
        $this->middleware('permission:Update Peluang', ['only' => ['update']]);
        $this->middleware('permission:Delete Peluang', ['only' => ['delete']]);
        $this->middleware('permission:UpdateTahap Peluang', ['only' => ['updateTahap']]);
        $this->middleware('permission:Restore Peluang', ['only' => ['restore']]);
        $this->middleware('permission:PA Peluang', ['only' => ['storePaymentAdvance']]);
        $this->middleware('permission:ForceDelete Peluang', ['only' => ['forceDelete']]);

    }

    public function index()
    {
        $user = Auth::user();

        // Implementasi Gate untuk validasi otorisasi akses tingkat halaman
        if (!Gate::allows('akses-filter-sales') && $user->jabatan !== 'Sales') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $salesList = [];

        // Evaluasi otorisasi spesifik untuk pemuatan data dropdown daftar sales
        if (in_array($user->jabatan, ['Adm Sales', 'SPV Sales'])) {
            $salesList = User::where('jabatan', 'Sales')
                ->where('status_akun', '1')
                ->select('id_sales', 'username')
                ->get();
        }

        // Variabel $materi dan $Perusahaan dihapus dari fungsi compact()
        return view('crm.peluang.index', compact('salesList'));
    }

    public function indexJson(Request $request)
    {
        try {
            $user = Auth::user();

            // 1. Integrasi Gate untuk otorisasi akses utama
            if (!Gate::allows('akses-crm')) {
                return response()->json(['error' => 'Unauthorized access.'], 403);
            }

            $draw = $request->input('draw');
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $searchValue = $request->input('search.value');
            $orderColumnIndex = $request->input('order.0.column', 11);
            $orderDir = $request->input('order.0.dir', 'desc');
            $statusFilter = $request->input('status_filter', 'aktif');

            $columns = [
                0 => 'id',
                4 => 'harga',
                5 => 'netsales',
                6 => 'pax',
                7 => 'periode_mulai',
                9 => 'tahap',
                10 => 'id_sales',
                11 => 'id',
            ];

            $orderColumn = $columns[$orderColumnIndex] ?? 'id';

            $query = Peluang::select('id', 'materi', 'harga', 'netsales', 'pax', 'periode_mulai', 'periode_selesai', 'tahap', 'created_at', 'id_rkm', 'id_sales');

            // 2. Integrasi Gate untuk restriksi visibilitas kueri data
            if (!Gate::allows('akses-filter-sales')) {
                $query->where('id_sales', $user->id_sales);
            }

            if ($statusFilter === 'lost') {
                $query->where('tahap', 'lost');
            } else {
                $query->where('tahap', '!=', 'lost');
            }

            $recordsTotal = $query->count();

            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('tahap', 'like', "%{$searchValue}%")
                      ->orWhere('id_sales', 'like', "%{$searchValue}%")
                      ->orWhereHas('materiRelation', function($qMateri) use ($searchValue) {
                          $qMateri->where('nama_materi', 'like', "%{$searchValue}%");
                      });
                });
            }

            $recordsFiltered = $query->count();

            $query->orderBy($orderColumn, $orderDir);
            if ($length != -1) {
                $query->offset($start)->limit($length);
            }

            $rawData = $query->with([
                'materiRelation',
                'rkm' => function($q) {
                    $q->withTrashed()->with('perusahaan');
                }
            ])->get();

            $peluangIds = $rawData->pluck('id')->toArray();
            $historiPeluang = [];

            if (!empty($peluangIds)) {
                $historiPeluang = DB::table('peluang_histories')
                    ->whereIn('id_peluang', $peluangIds)
                    ->pluck('id_peluang')
                    ->toArray();
            }

            $data = $rawData->map(function ($item) use ($historiPeluang) {
                $item->periode = $item->periode_mulai . ' s/d ' . $item->periode_selesai;

                $rkm = $item->rkm;
                $item->rkm_data = $rkm ? $rkm : null;

                $item->rkm_formatted = null;
                if ($rkm) {
                    $metode = 'vir';
                    if ($rkm->metode_kelas === 'Offline') {
                        $metode = 'off';
                    } elseif ($rkm->metode_kelas === 'Inhouse Bandung') {
                        $metode = 'inhb';
                    } elseif ($rkm->metode_kelas === 'Inhouse Luar Bandung') {
                        $metode = 'inhlb';
                    }

                    $item->rkm_formatted = [
                        'materi_key' => $rkm->materi_key,
                        'metode_kelas' => $metode,
                        'tanggal_awal_day' => $rkm->tanggal_awal ? date('d', strtotime($rkm->tanggal_awal)) : null,
                        'tanggal_awal_month' => $rkm->tanggal_awal ? date('n', strtotime($rkm->tanggal_awal)) : null,
                        'tanggal_awal_year' => $rkm->tanggal_awal ? date('Y', strtotime($rkm->tanggal_awal)) : null,
                    ];
                }

                $item->has_history = in_array($item->id, $historiPeluang);

                return $item;
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Terjadi kesalahan pada server.',
                    'message' => $e->getMessage()
                ],
                500
            );
        }
    }

    public function detail($id)
    {
        // 1. Eksekusi Nested Eager Loading tersentralisasi
        $peluang = Peluang::with([
            'materiRelation',
            'rkm' => function($query) { $query->withTrashed(); },
            'aktivitas.contact',
            'aktivitas.peserta',
            'perusahaan.contacts',
            'perusahaan.peserta'
        ])->findOrFail($id);

        // 2. Normalisasi atribut temporal RKM
        if ($peluang->rkm && $peluang->rkm->tanggal_awal) {
            $timestamp = strtotime($peluang->rkm->tanggal_awal);
            $peluang->rkm->tanggal_awal_day = date('d', $timestamp);
            $peluang->rkm->tanggal_awal_month = date('n', $timestamp);
            $peluang->rkm->tanggal_awal_year = date('Y', $timestamp);
        }

        // 3. Pengambilan dependensi entitas tunggal
        $materi = Materi::where('status', '!=', 'Nonaktif')->select('id', 'nama_materi')->get();

        $netsales = perhitunganNetSales::with('trackingNetSales', 'approvedNetSales', 'peserta')
            ->where('id_rkm', $peluang->id_rkm)
            ->first();

        $regis = Regisform::where('id_peluang', $id)->first();

        $perusahaan = $peluang->perusahaan;

        // 4. Transformasi dan penggabungan koleksi relasional ke memori
        $contactsItem = $perusahaan->contacts->map(function ($contact) {
            return [
                'id' => $contact->id,
                'nama' => $contact->nama,
                'type' => 'contact',
                'label' => "[Contact] {$contact->nama} (" . ($contact->email ?? 'Tidak ada email') . ")"
            ];
        });

        $pesertaItem = $perusahaan->peserta->map(function ($peserta) {
            return [
                'id' => $peserta->id,
                'nama' => $peserta->nama,
                'type' => 'peserta',
                'label' => "[Peserta] {$peserta->nama} (" . ($peserta->email ?? 'Tidak ada email') . ")"
            ];
        });

        $items = $contactsItem->concat($pesertaItem)->sortBy(function ($item) {
            return strtolower($item['label']);
        })->values()->all();

        $histories = \Illuminate\Support\Facades\DB::table('peluang_histories')
            ->where('id_peluang', $id)
            ->orderByDesc('created_at')
            ->get();

        // 5. Transmisi variabel koleksi final ke lapisan View
        return view('crm.peluang.detail', compact(
            'peluang',
            'materi',
            'netsales',
            'regis',
            'items',
            'histories'
        ));
    }
    // Pada method AmbilAktivitas($id)
    public function AmbilAktivitas($id)
    {
        // Ambil data perusahaan untuk referensi nama
        $perusahaanData = \App\Models\Perusahaan::select('id', 'nama_perusahaan')->find($id);
        $namaPerusahaan = $perusahaanData ? $perusahaanData->nama_perusahaan : '-';

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
            ->where(function ($query) use ($contactIds, $pesertaIds, $id) {
                if (!empty($contactIds)) {
                    $query->whereIn('id_contact', $contactIds);
                }
                if (!empty($pesertaIds)) {
                    $query->orWhereIn('id_peserta', $pesertaIds);
                }
                // Penyesuaian kueri untuk aktivitas PA
                $query->orWhere(function ($subQuery) use ($id) {
                    $subQuery->where('aktivitas', 'PA')
                            ->where('id_contact', $id);
                });
            })
            ->whereNull('id_peluang')
            ->orderByDesc('created_at')
            ->get();

        $result = $aktivitas->map(function ($a) use ($namaPerusahaan) {
            return [
                'id' => $a->id,
                // Modifikasi kondisi label kontak jika aktivitas = PA
                'kontak' => $a->aktivitas === 'PA' ? $namaPerusahaan : ($a->contact->nama ?? ($a->peserta->nama ?? '-')),
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
        $user = auth()->user();
        $allowedJabatan = ['Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting', 'GM', 'Direktur Utama', 'Direktur'];

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
            'perusahaan_pendaftar' => 'nullable|string|max:255',
            'id_sales' => 'nullable|string',
        ]);

        // Validasi data untuk tabel RKM
        $validatedRKM = $request->validate([
            'metode_kelas' => 'required|string|max:255',
            'event' => 'required|string|max:255',
            'exam' => 'required|in:0,1',
            'authorize' => 'required|in:0,1',
        ]);

        // Validasi duplikasi data
        $isDuplicate = Peluang::where('id_contact', $request->id_contact)
            ->where('materi', $request->materi)
            ->where('periode_mulai', $request->periode_mulai)
            ->where('periode_selesai', $request->periode_selesai)
            ->exists();

        if ($isDuplicate) {
            return back()->with([
                'error' => 'Data dengan perusahaan, materi, periode mulai, dan periode selesai yang sama sudah ada.',
            ])->withInput();
        }

        // LOGIKA PENENTUAN SALES PENANGGUNG JAWAB
        $finalIdSales = $user->id_sales ?? null;
        if (in_array($user->jabatan, $allowedJabatan) && $request->filled('id_sales')) {
            $finalIdSales = $request->id_sales;
        }

        // Parse tanggal dengan Carbon
        try {
            $start = Carbon::parse($request->input('periode_mulai'));
        } catch (\Exception $e) {
            return back()->withErrors(['periode_mulai' => 'Format tanggal periode_mulai tidak valid.']);
        }

        $bulanNamaMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
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

        // Siapkan data RKM
        $rkmData = array_merge($validatedRKM, [
            'sales_key' => $finalIdSales, // 🔹 Terapkan hasil logika id_sales yang sudah diotorisasi
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

        // Siapkan data Peluang
        $validated['id_rkm'] = $rkm ? $rkm->id : null;
        $validated['id_sales'] = $finalIdSales; // 🔹 Terapkan hasil logika id_sales yang sudah diotorisasi

        foreach (['periode_mulai', 'periode_selesai', 'netsales'] as $field) {
            if (empty($validated[$field])) {
                $validated[$field] = null;
            }
        }

        // Buat record Peluang
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
                'rkm.eksam',
                'rkm.outstanding',
                'rkm.registrasi',
                'rkm.analisisrkm'
            )->findOrFail($id);

            $deletedBy = Auth::user()->karyawan->kode_karyawan;
            $now = Carbon::now();

            if(!Auth::check()) {
                return back()->with([
                    'error' => 'Gagal menghapus peluang: User belum login.',
                ]);
            }

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

                if (!empty($rkm->eksam)) {
                    $rkm->eksam->update([
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

    public function forceDelete($id)
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('index.peluang')->with([
                    'error' => 'Gagal menghapus peluang: User belum login.',
                ]);
            }

            $peluang = Peluang::with([
                'rkm',
                'rkm.perhitunganNetSales',
                'rkm.eksam',
                'rkm.outstanding',
                'rkm.registrasi',
                'rkm.analisisrkm'
            ])->findOrFail($id);

            DB::beginTransaction();

            if ($peluang->rkm) {
                $rkm = $peluang->rkm;

                if ($rkm->perhitunganNetSales && $rkm->perhitunganNetSales->isNotEmpty()) {
                    foreach ($rkm->perhitunganNetSales as $item) {
                        $item->delete();
                    }
                }

                if ($rkm->registrasi && $rkm->registrasi->isNotEmpty()) {
                    foreach ($rkm->registrasi as $item) {
                        $item->delete();
                    }
                }

                if (!empty($rkm->eksam)) {
                    $rkm->eksam->delete();
                }

                if (!empty($rkm->outstanding)) {
                    $rkm->outstanding->delete();
                }

                if (!empty($rkm->analisisrkm)) {
                    $rkm->analisisrkm->delete();
                }

                $rkm->delete();
            }

            Aktivitas::where('id_peluang', $id)->delete();

            $peluang->delete();

            DB::commit();

            return redirect()->route('index.peluang')->with([
                'success' => 'Data Peluang beserta seluruh relasi berhasil dihapus secara permanen.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('index.peluang')->with([
                'error' => 'Gagal menghapus data secara permanen: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'id_perusahaan' => 'required|integer|exists:perusahaans,id',
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
                'perusahaan_pendaftar' => 'nullable|string|max:255',
            ]);

            // Validasi duplikasi data
            $isDuplicate = Peluang::where('id_contact', $request->id_perusahaan)
                ->where('materi', $request->materi)
                ->where('periode_mulai', $request->periode_mulai)
                ->where('periode_selesai', $request->periode_selesai)
                ->where('id', '!=', $id)
                ->exists();

            if ($isDuplicate) {
                return back()->with([
                    'error' => 'Data dengan perusahaan, materi, periode mulai, dan periode selesai yang sama sudah ada.',
                ])->withInput();
            }

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
            $rkm->perusahaan_key = $request->id_perusahaan;
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
                'id_contact' => $validated['id_perusahaan'],
                'materi' => $validated['materi'],
                'catatan' => $validated['catatan'],
                'harga' => $validated['harga'],
                'final' => $final,
                'netsales' => $final,
                'pax' => $validated['pax'],
                'periode_mulai' => $validated['periode_mulai'],
                'periode_selesai' => $validated['periode_selesai'],
                'tentatif' => $validated['tentatif'] ?? false,
                'perusahaan_pendaftar' => $validated['perusahaan_pendaftar'] ?? null,
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
            'rkm.eksam',
            'rkm.outstanding',
            'rkm.registrasi',
            'rkm.analisisrkm',
            'perusahaan',
            'rkm.materi'
        )->where('id', $id)->firstOrFail();

        DB::transaction(function () use ($peluang, $request) {
            $now = Carbon::now();
            $deletedBy = Auth::user()->karyawan->kode_karyawan;

            if(!Auth::check()) {
                return back()->with([
                    'error' => 'Gagal memperbarui tahap: User belum login.',
                ]);
            }

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
                $peluang->deleted_at = $now;
                $peluang->deleted_by = $deletedBy;

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

                    if (!empty($rkm->eksam)) {
                        $rkm->eksam->update([
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
                $netSalesCalculated = $inputFinal - ($inputFinal * 11 / 100);

                $peluang->final = $inputFinal;
                $peluang->netsales = $netSalesCalculated;

                $peluang->merah = $now;
                $peluang->desc_lost = null;

                if ($peluang->rkm) {
                    $peluang->rkm->status = '0';
                    $peluang->rkm->save();
                }

                $user = User::where('jabatan', 'Admin Holding')->where('status_akun', '1')->get();
                $data = [
                    'perusahaan' => $peluang->perusahaan->nama_perusahaan ?? 'N/A',
                    'materi' => $peluang->rkm->materi->nama_materi ?? 'N/A',
                    'periode' => $peluang->rkm?->tanggal_awal . ' - ' . $peluang->rkm?->tanggal_akhir ?? 'N/A',
                    'path' => $peluang->rkm->path ?? 'N/A',
                ];
                Notification::send($user, new PoReminder($data));

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
            'bukti' => 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf|max:5120',
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

        // Kalkulasi total Payment Advance
        $total_pa = (float)($request->transportasi ?? 0) +
                    (float)($request->akomodasi_peserta ?? 0) +
                    (float)($request->akomodasi_tim ?? 0) +
                    (float)($request->fresh_money ?? 0) +
                    (float)($request->entertaint ?? 0) +
                    (float)($request->souvenir ?? 0) +
                    (float)($request->cashback ?? 0) +
                    (float)($request->sewa_laptop ?? 0);

        // Save payment advance baru
        Log::info("[PA] Saving new Net Sales record");

        $netSales = new perhitunganNetSales();
        $netSales->id_rkm = $request->id_rkm;

        $netSales->transportasi = $request->transportasi ?: null;
        $netSales->jenis_transportasi = $request->jenis_transportasi;

        $netSales->akomodasi_peserta = $request->akomodasi_peserta ?: null;
        $netSales->akomodasi_tim = $request->akomodasi_tim ?: null;
        $netSales->keterangan_akomodasi_tim = $request->keterangan_akomodasi_tim ?: null;

        $netSales->fresh_money = $request->fresh_money ?: null;
        $netSales->entertaint = $request->entertaint ?: null;
        $netSales->keterangan_entertaint = $request->keterangan_entertaint ?: null;
        $netSales->souvenir = $request->souvenir ?: null;
        $netSales->cashback = $request->cashback ?: null;

        $netSales->sewa_laptop = $request->sewa_laptop ?: null;
        $netSales->tipe_pembayaran = $request->tipe_pembayaran;
        $netSales->tgl_pa = $request->tgl_pa;
        $netSales->deskripsi_tambahan = $request->deskripsi_tambahan;

        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $filename = 'bukti_pembayaran_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('bukti_pa', $filename, 'public');
            $netSales->bukti = $path;
        }

        $netSales->id_tracking = $idTracking;
        $netSales->save();

        Log::info("[PA] Net Sales saved", ['net_sales_id' => $netSales->id]);

        // Memperbarui record Netsales lama dengan mengurangi nilai final
        if ($existingNetSales) {
            $existingNetSales->final = (float)$existingNetSales->final - $total_pa;
            $existingNetSales->save();

            Log::info("[PA] Existing Net Sales final value updated", [
                'total_pa_deducted' => $total_pa,
                'new_final_value' => $existingNetSales->final
            ]);
        }

        // Memperbarui record Netsales pada tabel Peluang
        $peluang = \App\Models\Peluang::find($request->id_peluang);
        if ($peluang) {
            $peluang->netsales = (float)$peluang->netsales - $total_pa;
            $peluang->save();

            Log::info("[PA] Peluang Net Sales updated", [
                'id_peluang' => $peluang->id,
                'total_pa_deducted' => $total_pa,
                'new_netsales_value' => $peluang->netsales
            ]);
        } else {
            Log::warning("[PA] Peluang not found for updating Net Sales", ['id_peluang' => $request->id_peluang]);
        }

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

    public function restore(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'tahap_baru' => 'required|in:biru,merah',
                'harga' => 'required|numeric|min:0',
                'pax' => 'required|integer|min:1',
                'periode_mulai' => 'required|date',
                'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
            ]);

            DB::beginTransaction();

            $peluang = Peluang::findOrFail($id);

            if ($peluang->tahap !== 'lost') {
                throw new \Exception('Hanya peluang dengan status lost yang dapat dipulihkan.');
            }

            // 1. Simpan Histori Perubahan
            DB::table('peluang_histories')->insert([
                'id_peluang' => $peluang->id,
                'tahap_sebelumnya' => $peluang->tahap,
                'tahap_baru' => $validated['tahap_baru'],
                'harga_sebelumnya' => $peluang->harga,
                'harga_baru' => $validated['harga'],
                'pax_sebelumnya' => $peluang->pax,
                'pax_baru' => $validated['pax'],
                'periode_mulai_sebelumnya' => $peluang->periode_mulai,
                'periode_mulai_baru' => $validated['periode_mulai'],
                'periode_selesai_sebelumnya' => $peluang->periode_selesai,
                'periode_selesai_baru' => $validated['periode_selesai'],
                'keterangan' => 'Pemulihan dari status lost',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Kalkulasi Netsales
            $final = $validated['harga'] - ($validated['harga'] * 11 / 100);

            // 3. Perbarui dan Pulihkan Entitas Peluang
            $peluang->tahap = $validated['tahap_baru'];
            $peluang->{$validated['tahap_baru']} = now(); // Perbarui timestamp biru atau merah
            $peluang->lost = null; // Hapus penanda lost
            $peluang->deleted_at = null; // Hapus penanda soft delete
            $peluang->deleted_by = null; // Hapus penanda soft delete
            $peluang->harga = $validated['harga'];
            $peluang->final = $final;
            $peluang->netsales = $final;
            $peluang->pax = $validated['pax'];
            $peluang->periode_mulai = $validated['periode_mulai'];
            $peluang->periode_selesai = $validated['periode_selesai'];
            $peluang->save();

            // 4. Perbarui dan Pulihkan Entitas RKM beserta Relasinya
            // Gunakan withTrashed() untuk menemukan RKM yang telah di-soft delete
            $rkm = RKM::withTrashed()->where('id', $peluang->id_rkm)->first();

            if ($rkm) {
                // Pulihkan RKM utama
                $rkm->deleted_at = null;
                $rkm->deleted_by = null;
                $rkm->harga_jual = $validated['harga'];
                $rkm->pax = $validated['pax'];
                $rkm->isi_pax = $validated['pax'];
                $rkm->tanggal_awal = $validated['periode_mulai'];
                $rkm->tanggal_akhir = $validated['periode_selesai'];
                $rkm->save();

                // Pulihkan entitas turunan RKM (menggunakan mass-update untuk efisiensi)
                perhitunganNetSales::where('id_rkm', $rkm->id)->update([
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);

                Registrasi::where('id_rkm', $rkm->id)->update([
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);

                eksam::where('id_rkm', $rkm->id)->update([
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);

                outstanding::where('id_rkm', $rkm->id)->update([
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);

                kelasanalisis::where('id_rkm', $rkm->id)->update([
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);
            }

            // 5. Pulihkan Entitas Aktivitas
            Aktivitas::where('id_peluang', $id)->update([
                'deleted_at' => null,
                'deleted_by' => null,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Peluang beserta seluruh relasi RKM berhasil dipulihkan.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memulihkan peluang: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchPerusahaan(Request $request)
    {
        $search = $request->input('q');
        $user = Auth::user();

        $query = Perusahaan::select('id', 'nama_perusahaan', 'cp');

        if ($user->jabatan === 'Sales') {
            $query->where('sales_key', $user->id_sales);
        }

        if (!empty($search)) {
            $query->where('nama_perusahaan', 'like', "%{$search}%");
        }

        // Membatasi hasil maksimal 20 rekaman per kueri untuk stabilitas performa
        $perusahaan = $query->limit(20)->get();

        $formattedData = [];
        foreach ($perusahaan as $item) {
            $formattedData[] = [
                'id' => $item->id,
                'text' => $item->nama_perusahaan . ' (' . $item->cp . ')'
            ];
        }

        return response()->json($formattedData);
    }

    public function searchMateri(Request $request)
    {
        $search = $request->input('q');

        $query = Materi::select('id', 'nama_materi')->where('status', '!=', 'Nonaktif');

        if (!empty($search)) {
            $query->where('nama_materi', 'like', "%{$search}%");
        }

        // Membatasi hasil maksimal 20 rekaman per kueri untuk stabilitas performa
        $materi = $query->limit(20)->get();

        $formattedData = [];
        foreach ($materi as $item) {
            $formattedData[] = [
                'id' => $item->id,
                'text' => $item->nama_materi
            ];
        }

        return response()->json($formattedData);
    }

}
