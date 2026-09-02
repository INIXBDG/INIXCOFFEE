<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\karyawan;
use App\Models\lokasi;
use App\Models\Materi;
use App\Models\Peluang;
use App\Models\Perusahaan;
use App\Models\RKM;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\RiwayatStatusPerusahaan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View CRM History Status', ['only' => ['allHistoryStatus', 'allHistoryStatusData']]);
        $this->middleware('permission:View Contact CRM', ['only' => ['index', 'getPerusahaan', 'detail']]);
        $this->middleware('permission:Store Contact CRM', ['only' => ['store']]);
        $this->middleware('permission:Update Contact CRM', ['only' => ['update']]);
        $this->middleware('permission:Delete Contact CRM', ['only' => ['delete']]);
    }

    public function index()
    {
        $lokasi = lokasi::select('id', 'lokasi')->get();

        $sales = karyawan::where('jabatan', 'sales')
            ->where('status_aktif', '1')
            ->select('kode_karyawan', 'nama_lengkap')
            ->get();

        return view('crm.contact.index', compact('lokasi', 'sales'));
    }

    public function getPerusahaan(Request $request)
    {
        if (Gate::denies('akses-crm-perusahaan')) {
            return response()->json([
                'error' => 'Anda tidak memiliki akses ke data ini.'
            ], 403);
        }

        try {
            $user = auth()->user();

            $draw = intval($request->input('draw', 1));
            $startLimit = intval($request->input('start', 0));
            $length = intval($request->input('length', 10));
            $searchValue = $request->input('search.value');

            $columns = [
                'id',
                'nama_perusahaan',
                'lokasi',
                'status',
                'sales_key',
                'id',
                'id'
            ];

            $orderColumn = $columns[$request->input('order.0.column', 0)] ?? 'id';
            $orderDir = $request->input('order.0.dir', 'desc');

            $query = Perusahaan::select(
                'id',
                'nama_perusahaan',
                'npwp',
                'alamat',
                'kategori_perusahaan',
                'lokasi',
                'email',
                'status',
                'sales_key'
            );

            if ($user->jabatan === 'Sales') {
                $query->where('sales_key', $user->id_sales);
            }

            if ($request->filled('sales_key')) {
                $query->where('sales_key', $request->input('sales_key'));
            }

            $recordsTotal = $query->count();

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('nama_perusahaan', 'like', "%{$searchValue}%")
                    ->orWhere('lokasi', 'like', "%{$searchValue}%")
                    ->orWhere('sales_key', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
                });

                $recordsFiltered = $query->count();
            } else {
                $recordsFiltered = $recordsTotal;
            }

            $query->orderBy($orderColumn, $orderDir);

            if ($length > 0) {
                $query->offset($startLimit)->limit($length);
            }

            $query->with('kelasTerakhir.materi:id,nama_materi')
                ->addSelect([
                    'aktivitas_terakhir_date' =>
                        Aktivitas::select('created_at')
                            ->whereIn('id_contact', function ($q) {
                                $q->select('id')
                                    ->from('contacts')
                                    ->whereColumn('id_perusahaan', 'perusahaans.id');
                            })
                            ->orderBy('created_at', 'desc')
                            ->limit(1)
                ]);

            $data = $query->get();

            $responseData = $data->map(function ($contact) {
                $rkm = $contact->kelasTerakhir;

                return [
                    'id' => $contact->id,
                    'nama_perusahaan' => $contact->nama_perusahaan,
                    'lokasi' => $contact->lokasi,
                    'status' => $contact->status,
                    'sales_key' => $contact->sales_key,
                    'kelas_terakhir' => $rkm?->materi?->nama_materi ?? 'Belum ada kelas',
                    'kelas_terakhir_date' => $rkm
                        ? \Carbon\Carbon::parse($rkm->created_at)->translatedFormat('d F Y')
                        : null,
                    'aktivitas_terakhir_date' => $contact->aktivitas_terakhir_date
                        ? \Carbon\Carbon::parse($contact->aktivitas_terakhir_date)->format('d-m-Y')
                        : 'Belum ada aktivitas',
                    'npwp' => $contact->npwp,
                    'alamat' => $contact->alamat,
                    'kategori_perusahaan' => $contact->kategori_perusahaan,
                    'email' => $contact->email,
                ];
            });

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $responseData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Kesalahan server',
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function detail($id)
    {
        $data = Perusahaan::with(['contacts', 'peserta.latestRegistrasi.materi'])->where('id', $id)->firstOrFail();

        $items = [];

        // Tambahkan semua contacts
        foreach ($data->contacts as $contact) {
            $items[] = [
                'id' => $contact->id,
                'nama' => $contact->nama,
                'type' => 'contact',
                'label' => "[Contact] " . $contact->nama . " (" . ($contact->email ?? 'Tidak ada email') . ")"
            ];
        }

        // Tambahkan semua peserta
        foreach ($data->peserta as $peserta) {
            $items[] = [
                'id' => $peserta->id,
                'nama' => $peserta->nama,
                'type' => 'peserta',
                'label' => "[Peserta] " . $peserta->nama. " (" . ($peserta->email ?? 'Tidak ada email') . ")"
            ];
        }

        // Urutkan berdasarkan nama
        usort($items, function ($a, $b) {
            return strcasecmp($a['label'], $b['label']);
        });

        // Ambil data lainnya (jika diperlukan)
        $aktivitass = Aktivitas::with(['contact', 'peserta'])
            ->where(function ($query) use ($data) {
                $query->whereIn('id_contact', $data->contacts->pluck('id'))
                    ->orWhereIn('id_peserta', $data->peserta->pluck('id'));
            })
            ->orderByDesc('created_at')
            ->get();

        $aktivitas = Aktivitas::with(['contact', 'peserta'])
            ->where(function ($query) use ($data) {
                $query->whereIn('id_contact', $data->contacts->pluck('id'))
                    ->orWhereIn('id_peserta', $data->peserta->pluck('id'));
            })
            ->whereNull('id_peluang')
            ->orderByDesc('created_at')
            ->get();


        $peluang = Peluang::where('id_contact', $data->id)
            ->with('materiRelation')
            ->get();

            // dd($items);

        $materi = Materi::all();


        return view('crm.contact.detail', compact('data', 'items', 'aktivitas','aktivitass', 'peluang', 'materi'));
    }

    public function store(Request $request)
    {
        // Validasi input sesuai field perusahaan
        $validated = $request->validate([
            'nama_perusahaan'      => 'required|string|max:255',
            'kategori_perusahaan'  => 'nullable|string|max:255',
            'lokasi'               => 'nullable|string|max:255',
            // 'sales_key'            => 'nullable|string|max:255',
            'status'               => 'nullable|string|max:255',
            'npwp'                 => 'nullable|string|max:255',
            'alamat'               => 'nullable|string|max:1000',
            'cp'                   => 'nullable|string|max:20',
            'no_telp'              => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:255',
            'foto_npwp'            => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        // Handle upload foto_npwp, jika ada
        if ($request->hasFile('foto_npwp')) {
            $file = $request->file('foto_npwp');
            $extension = $file->getClientOriginalExtension();
            $filename = $validated['nama_perusahaan'] . '_npwp.' . $extension;
            $file->storeAs('public/npwp', $filename);
            $validated['foto_npwp'] = $filename;
        }

        // Menambahkan id_sales dari input manual atau default dari user login
        $id_sales = $request->input('id_sales', auth()->user()->id_sales ?? null);

        $validated['sales_key'] = $id_sales;

        // Simpan data perusahaan
        $perusahaan = Perusahaan::create($validated + ['sales_key' => $id_sales]);

        $aktivitas = new Aktivitas();
        $aktivitas->id_sales = $id_sales;
        $aktivitas->aktivitas = 'DB';
        $aktivitas->deskripsi = 'Database baru "' . $perusahaan->nama_perusahaan . '" berhasil ditambahkan';
        $aktivitas->waktu_aktivitas = Carbon::now();
        $aktivitas->save();

        return back()->with([
            'message' => 'Data perusahaan berhasil disimpan.',
            'data' => $perusahaan,
            'aktivitas' => $aktivitas,
        ]);
    }

    public function delete($id)
    {
        $contact = Perusahaan::where('id', $id)->first();
        $contact->delete();

        return back()->with([
            'message' => 'Kontak berhasil dihapus.',
        ]);
    }

    public function update($id, Request $request)
    {
        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'kategori_perusahaan' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'lokasi' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'alamat' => 'nullable|string|max:500',
            'no_telp' => 'nullable|string|max:20',
            'cp' => 'nullable|string|max:100',
            'foto_npwp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $contact = Perusahaan::findOrFail($id);

        // Pencatatan riwayat menggunakan relasi tabel baru
        if (!empty($contact->status) && $contact->status !== $validated['status']) {
            $contact->riwayatStatus()->create([
                'status_lama' => $contact->status,
                'status_baru' => $validated['status'],
                'waktu_perubahan' => now(),
                'diubah_oleh' => auth()->check() ? auth()->user()->id_sales : 'sistem'
            ]);
        }

        $contact->nama_perusahaan = $validated['nama_perusahaan'];
        $contact->kategori_perusahaan = $validated['kategori_perusahaan'];
        $contact->email = $validated['email'];
        $contact->lokasi = $validated['lokasi'] ?? $contact->lokasi;
        $contact->status = $validated['status'];
        $contact->npwp = $validated['npwp'] ?? $contact->npwp;
        $contact->alamat = $validated['alamat'] ?? $contact->alamat;
        $contact->no_telp = $validated['no_telp'] ?? $contact->no_telp;
        $contact->cp = $validated['cp'] ?? $contact->cp;

        if ($request->hasFile('foto_npwp')) {
            $file = $request->file('foto_npwp');
            $extension = $file->getClientOriginalExtension();
            $filename = $validated['nama_perusahaan'] . '_npwp.' . $extension;
            $file->storeAs('public/npwp', $filename);
            $contact->foto_npwp = 'npwp/' . $filename;
        }

        $contact->save();

        return back()->with([
            'message' => 'Kontak berhasil diperbarui.',
        ]);
    }

    // 1. Fungsi Utama Hanya Memuat View Secara Instan
    public function allHistoryStatus()
    {
        return view('crm.contact.all_history_status');
    }

    public function apiHistoryAnalytics()
    {
        $analytics = Cache::remember('history_status_analytics', 3600, function () {
            $totalConversionDays = 0;
            $conversionCount = 0;
            $transitionRate = [];
            $timeBasedTrends = [];

            // 1. Kalkulasi Durasi Konversi (Agregasi SQL)
            $conversionData = RiwayatStatusPerusahaan::select(
                    'perusahaan_id',
                    DB::raw('MIN(waktu_perubahan) as first_date'),
                    DB::raw('MAX(waktu_perubahan) as last_date')
                )
                ->groupBy('perusahaan_id')
                ->havingRaw('COUNT(id) > 1')
                ->get();

            foreach ($conversionData as $data) {
                $firstDate = strtotime($data->first_date);
                $lastDate = strtotime($data->last_date);
                $diffDays = ($lastDate - $firstDate) / (60 * 60 * 24);
                $totalConversionDays += $diffDays;
                $conversionCount++;
            }

            // 2. Kalkulasi Rasio Transisi (Agregasi SQL)
            $transitions = RiwayatStatusPerusahaan::select(
                    'status_lama',
                    'status_baru',
                    DB::raw('COUNT(id) as total')
                )
                ->groupBy('status_lama', 'status_baru')
                ->get();

            foreach ($transitions as $t) {
                $lama = $t->status_lama ?? '-';
                $baru = $t->status_baru ?? '-';
                $key = $lama . ' -> ' . $baru;
                $transitionRate[$key] = $t->total;
            }
            arsort($transitionRate);

            // 3. Kalkulasi Tren Tanggal (Agregasi SQL)
            $trends = RiwayatStatusPerusahaan::select(
                    DB::raw('DATE(waktu_perubahan) as tanggal'),
                    DB::raw('COUNT(id) as total')
                )
                ->whereNotNull('waktu_perubahan')
                ->groupBy(DB::raw('DATE(waktu_perubahan)'))
                ->orderBy('tanggal', 'asc')
                ->get();

            foreach ($trends as $trend) {
                $timeBasedTrends[$trend->tanggal] = $trend->total;
            }

            return [
                'averageConversionDays' => $conversionCount > 0 ? round($totalConversionDays / $conversionCount, 2) : 0,
                'transitionRate' => $transitionRate,
                'timeBasedTrends' => $timeBasedTrends
            ];
        });

        return response()->json($analytics);
    }

    public function allHistoryStatusData(Request $request)
    {
        // 1. Inisialisasi Kueri Dasar dengan Join ke Tabel Perusahaan
        $query = RiwayatStatusPerusahaan::join('perusahaans', 'riwayat_status_perusahaans.perusahaan_id', '=', 'perusahaans.id')
            ->select(
                'riwayat_status_perusahaans.waktu_perubahan',
                'riwayat_status_perusahaans.status_lama',
                'riwayat_status_perusahaans.status_baru',
                'perusahaans.nama_perusahaan'
            );

        // 2. Hitung Total Data Keseluruhan (Sebelum Filter)
        $recordsTotal = RiwayatStatusPerusahaan::count();

        // 3. Eksekusi Pencarian (Filtering)
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $query->where(function($q) use ($searchValue) {
                $q->where('perusahaans.nama_perusahaan', 'like', "%{$searchValue}%")
                  ->orWhere('riwayat_status_perusahaans.status_lama', 'like', "%{$searchValue}%")
                  ->orWhere('riwayat_status_perusahaans.status_baru', 'like', "%{$searchValue}%");
            });
        }

        // 4. Hitung Total Data Setelah Filter
        $recordsFiltered = $query->count();

        // 5. Pengurutan Data (Sorting) secara Default
        $query->orderBy('riwayat_status_perusahaans.waktu_perubahan', 'desc');

        // 6. Batasan Paginasi (Limit & Offset) sesuai Permintaan DataTables
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        if ($length > 0) {
            $query->offset($start)->limit($length);
        }

        $data = $query->get();

        // 7. Pengembalian Respons dengan Format Standar DataTables
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $allowedJabatan = [
            'Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting',
            'GM', 'Sales', 'Direktur Utama', 'Direktur'
        ];

        $salesName = null;

        if ($user->jabatan === 'Sales') {
            $idSales = $user->id_sales;
            $baseQuery = Perusahaan::with('contacts')->where('sales_key', $idSales);

            // Ekstraksi nama lengkap untuk user Sales yang sedang login
            if ($user->karyawan) {
                $salesName = $user->karyawan->nama_lengkap;
            }
        } elseif (in_array($user->jabatan, $allowedJabatan)) {
            $baseQuery = Perusahaan::with('contacts');
        } else {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        // Ekstraksi nama lengkap berdasarkan filter jika user bukan Sales
        if ($request->filled('sales_key') && $user->jabatan !== 'Sales') {
            $baseQuery->where('sales_key', $request->sales_key);

            $salesUser = User::with('karyawan')->where('id_sales', $request->sales_key)
                ->orWhereHas('karyawan', function ($q) use ($request) {
                    $q->where('kode_karyawan', $request->sales_key);
                })->first();

            if ($salesUser && $salesUser->karyawan) {
                $salesName = $salesUser->karyawan->nama_lengkap;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%$search%")
                    ->orWhere('lokasi', 'like', "%$search%")
                    ->orWhere('sales_key', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
            });
        }

        $data = $baseQuery->orderBy('id', 'desc')->get();

        $kelasTerakhir = [];
        $aktivitasTerakhir = [];

        foreach ($data as $item) {
            $kelasTerakhir[$item->id] = RKM::where('perusahaan_key', $item->id)
                ->latest()
                ->with('materi')
                ->first();

            $contactIds = $item->contacts->pluck('id');

            $aktivitasTerakhir[$item->id] = Aktivitas::whereIn('id_contact', $contactIds)
                ->latest()
                ->first();
        }

        $pdf = Pdf::loadView('crm.contact.pdf', compact('data', 'kelasTerakhir', 'aktivitasTerakhir', 'salesName'));

        // Pembentukan nama file dinamis
        $fileName = 'Data_Database_Client';
        if ($salesName) {
            // Mengganti spasi dengan underscore untuk standar penamaan file
            $fileName .= '_' . str_replace(' ', '_', $salesName);
        }
        $fileName .= '.pdf';

        return $pdf->download($fileName);
    }
}
